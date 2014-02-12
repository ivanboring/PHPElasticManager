<?php

/**
 * The router.php keeps track of which modules to run based on the q
 * parameter.
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class Router
{
	/**
     * The viewport object
     *
     * @var viewport object
     */
    protected static $viewport = null;

	/**
     * The query object
     *
     * @var query object
     */	
    protected static $query_loader = null;

	/**
     * The configuration array
     *
     * @var array
     */		
    protected static $config = array();




	static function appendMessage($message){
		$_SESSION["messages"] = isset($_SESSION["messages"])?array():$_SESSION["messages"];
		$_SESSION["messages"][] = $message;
	}
	
	static function getMessages(){
		if(isset($_SESSION["messages"])){
			$messages = $_SESSION["messages"];
			unset($_SESSION["messages"]);
			return $messages;
		}
		return FALSE;
	}
	/**
     * The main routing function. Keeps track
	 * on which controllers and views to run.
     *
     * @param array $config The configuration array
     */
	public function route($config)
	{
        self::$query_loader = new Query($config['servers']['host'], $config['servers']['port']);
        self::$viewport = new Viewport();
        self::$config = $config;

        // Verify that PHPElasticManager is installed
        $this->verifyInstalled();
		
		// Get the location from the q parameter
        $location = $this->getLocation();

        // Verify that the file exists
        $this->verifyClassExists($location['master']);

        // If we are not in query phase, remove all instances of manual queries
        $response_message = '';
        if ($location['master'] != 'query') {
            $response_message = isset($_SESSION['query_response']) ? $_SESSION['query_response'] : '';
            unset($_SESSION['query_response']);
            unset($_SESSION['query_path']);
            unset($_SESSION['query_method']);
            unset($_SESSION['query_query']);
            unset($_SESSION['query_redirect']);
        }

        // Check if logged in else redirect to login page
        if ($this->loggedIn() == false && $location['master'] != 'user') {
            $this->redirect('user/login');
        }
		
		// Load the controller file
        require_once 'modules/'. $location['master'] . '/controller/' . $location['master'] . '.php';

        $controller_name = 'controller' . $location['master'];
		
        // Load the controller
        $controller = new $controller_name();
		
        // Check if the method exists
        if (method_exists($controller, 'page_' . $location['secondary'])) {
            // Run the controller
            $vars = $controller->{'page_' . $location['secondary']}($location['args']);
        } else {
        	// Run index from the first parameter
            array_unshift($location['args'], $location['secondary']);
            $vars = $controller->page_index($location['args']);
        }
		
		// Get the menus from the controllers
        $vars['menus'] = $this->getMenus();
		
		// If response message exists output it
        $vars['response_message'] = '';
        if ($response_message) {
            $vars['response_message'] = self::$query_loader->prettyJson(json_encode($response_message));
        }

        // Only render if return values exists
        if (isset($vars['content'])) {
            // Check validation mode
            $vars['validate'] = isset($_SESSION['query_validation']) && $_SESSION['query_validation'] ? 'checked' : '';

            // Always load the leftblock
            $vars['leftblock'] = '';
            if ($this->loggedIn()) {
                $left_block = $this->loadLeftBlock();
                $vars['leftblock'] = $left_block['content'];
            }
			
			// Render the last things
            self::$viewport->render('page', $vars, false);
            self::$viewport->createPage();
        }		
	}

	/**
     * This function renders and returs a view.
	 * Uses get_called_class to know which
	 * controller started it.
     *
     * @param string $view The name of the template
	 * @param array $vars Variables that should be passed to the template
	 * 
	 * @return string The rendered template
     */
    public function renderPart($view, $vars = array())
    {
        return self::$viewport->render($view, $vars, true, strtolower(str_replace('controller', '', get_called_class())));
    }

	/**
     * Loads and renders the left block
     *
     * @return array The rendered left block 
     */
    public function loadLeftBlock()
    {
        require_once 'modules/leftblock/controller/leftblock.php';

        $left_block = new controllerLeftblock();

        return $left_block->block_create();
    }

	/**
     * Loads all modules and check if they
	 * want to add items to the menu
     *
     * @return array The menu items 
     */
    public function getMenus()
    {
        $start_menu = array();

        // Load all the controllers to check for menu items if the user is logged in
        if ($this->loggedIn()) {
            $dirs = scandir('modules');
            foreach ($dirs as $dir) {
                if (substr($dir, 0, 1) != '.') {
                    require_once 'modules/'. $dir . '/controller/' . $dir . '.php';

                    $controller_name = 'controller' . $dir;

                    $controller = new $controller_name();

                    if (method_exists($controller, 'menu_items')) {
                        $menu = $controller->menu_items();
                        $start_menu[$menu['weight'] . '_' . $menu['path']] = array(
                            'title' => $menu['title'],
                            'path' => $menu['path']
                        );
                    }
                }
            }

            krsort($start_menu);

            $start_menu['100_user/logout'] = array('title' => 'Logout', 'path' => 'user/logout');
        }

        return $start_menu;
    }

	/**
     * Checks if the user is logged in
     *
     * @return bool Set to true if logged in
     */
    private function loggedIn()
    {
        return !isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] ? false : true;
    }

	/**
     * Checks if the user is logged in
     *
     * @return array An array with master (controller), secondary (function), args (arguments)
     */
    public function getLocation()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        parse_str($query_string, $query_parts);

        if (!isset($query_parts['q']) || !$query_parts['q']) {
            $query_parts['q'] = 'start';
        }

        $path =  $this->removeDirectoryTraversal($query_parts['q']);

        $parts = explode('/', $path);

        $output['master'] = isset($parts[0]) ? $parts[0] : 'start';
        $output['secondary'] = isset($parts[1]) ? $parts[1] : 'index';
        if (isset($parts[0])) { array_shift($parts); }
        if (isset($parts[0])) { array_shift($parts); }

        $output['args'] = $parts;

        return $output;
    }

	/**
     * This function redirects the user
     *
     * @param string $place The place to redirect to
	 * @param array $querystring Additional querystring to append to the redirect
     */
    protected function redirect($place = '', $querystring = array())
    {
        $place = $place ? $place : $_GET['q'];
        if (count($querystring)) {
            $place .= '&' . http_build_query($querystring);
        }
        header('location: ?q=' . $place);
        exit;
    }

	/**
     * Trims and removes directory traversals from a string
	 * 
	 * @param string $path The path to fix
     *
     * @return string A fixed string
     */
    public function removeDirectoryTraversal($path)
    {
        return trim(str_replace('..', '', $path), '/');
    }

	/**
     * Verifies if a class exists
	 * 
	 * @param string $classname The classname
     */
    public function verifyClassExists($classname)
    {
        if (!file_exists('modules/' . $classname . '/controller/' . $classname . '.php')) {
            echo "No such controller";
            exit;
        }
    }

	/**
     * Gets the querystring value for a key
	 * 
	 * @param string $key The key to look for
	 * @param string $default_value The default value if the key is empty
     *
     * @return string The value or default value of the key
     */
    protected function getString($key, $default_value = '')
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default_value;
    }

	/**
     * Gets the post value for a key
	 * 
	 * @param string $key The key to look for
	 * @param string $default_value The default value if the key is empty
     *
     * @return string The value or default value of the key
     */
    protected function getPost($name, $default_value = '')
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default_value;
    }

	/**
     * Checks if PHPElasticManager is correctly installed
	 * 
     */
    private function verifyInstalled()
    {
        $installmode = false;

        if (isset($this::$config['users']['user']) && $this::$config['users']['user']) {
            $installmode['response_message'] = 'Go into the config.php and change the node values to your liking. Also setup some user(s)
according to the example.';
            $installmode['mode'] = true;
        }

        $uri = $_SERVER['REQUEST_URI'];
        $parts = explode('/', $uri);
        if (in_array('public', $parts)) {
            if (!file_exists('.htaccess')) {
                $installmode['response_message'] = 'For added security, setup your vhost so that the visible root directory is public.
If you don\'t have that possibility copy the .htaccess file located under core/.htaccess to the
root directory.';
                $installmode['mode'] = true;
            }
        }

        if ($installmode['mode']) {
            $installmode['menus'] = array();
            $installmode['title'] = 'Installation';
            $installmode['content'] = '';
            self::$viewport->render('page', $installmode, false);
            self::$viewport->createPage();
            exit;
        }
    }
}
