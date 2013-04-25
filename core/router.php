<?php

require_once 'core/autoload.php';

// Make the l a function so we don't have to think about objects in the view files
function l($link, $html, $vars = array())
{
    $url = new url;

    return $url->createUrl($link, $html, $vars);
}

class router
{
    public static $viewport = '';
    public static $queryLoader = '';
    public static $config = array();

    public function __construct($config)
    {
        self::$queryLoader = new query($config['servers']['host'], $config['servers']['port']);
        self::$viewport = new viewport();
        self::$config = $config;

        // Verify that it's installed
        $this->verifyInstalled();

        $location = $this->getLocation();

        // Verify that the file exists
        $this->verifyClassExists($location['master']);

        // If we are not in query, remove all instances of manual queries
        $response_message = '';
        if ($location['master'] != 'query') {
            $response_message = isset($_SESSION['query_response']) ? $_SESSION['query_response'] : '';
            unset($_SESSION['query_response']);
            unset($_SESSION['query_path']);
            unset($_SESSION['query_method']);
            unset($_SESSION['query_query']);
            unset($_SESSION['query_redirect']);
        }

        // Check login
        if ($this->loggedIn() == false && $location['master'] != 'user') {
            $this->redirect('user/login');
        }

        require_once 'modules/'. $location['master'] . '/controller/' . $location['master'] . '.php';

        $controllername = 'controller' . $location['master'];

        // Load the controller
        $controller = new $controllername();

        // Check if the method exists
        if (method_exists($controller, 'page_' . $location['secondary'])) {
            // Run the controller
            $vars = $controller->{'page_' . $location['secondary']}($location['args']);
        } else {
            array_unshift($location['args'], $location['secondary']);
            $vars = $controller->page_index($location['args']);
        }

        $vars['menus'] = $this->getMenus();

        $vars['response_message'] = '';
        if ($response_message) {
            $vars['response_message'] = self::$queryLoader->prettyJson(json_encode($response_message));
        }

        // Only render if return values exists
        if (isset($vars['content'])) {
            // Check validation mode
            $vars['validate'] = isset($_SESSION['query_validation']) && $_SESSION['query_validation'] ? 'checked' : '';

            // Always load the leftblock
            $vars['leftblock'] = '';
            if ($this->loggedIn()) {
                $leftblock = $this->loadLeftBlock();
                $vars['leftblock'] = $leftblock['content'];
            }

            self::$viewport->render('page', $vars, false);
            self::$viewport->createPage();
        }
    }

    public function renderPart($view, $vars = array())
    {
        return self::$viewport->render($view, $vars, true, strtolower(str_replace('controller', '', get_called_class())));
    }

    public function loadLeftBlock()
    {
        require_once 'modules/leftblock/controller/leftblock.php';

        $leftblock = new controllerLeftblock();

        return $leftblock->block_create();
    }

    public function getMenus()
    {
        $startmenu = array();

        // Load all the controllers to check for menu items if the user is logged in
        if ($this->loggedIn()) {
            $dirs = scandir('modules');
            foreach ($dirs as $dir) {
                if (substr($dir, 0, 1) != '.') {
                    require_once 'modules/'. $dir . '/controller/' . $dir . '.php';

                    $controllername = 'controller' . $dir;

                    $controller = new $controllername();

                    if (method_exists($controller, 'menu_items')) {
                        $menu = $controller->menu_items();
                        $startmenu[$menu['weight'] . '_' . $menu['path']] = array(
                            'title' => $menu['title'],
                            'path' => $menu['path']
                        );
                    }
                }
            }

            krsort($startmenu);

            $startmenu['100_user/logout'] = array('title' => 'Logout', 'path' => 'user/logout');
        }

        return $startmenu;
    }

    private function loggedIn()
    {
        return !isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] ? false : true;
    }

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

    protected function redirect($place = '', $querystring = array())
    {
        $place = $place ? $place : $_GET['q'];
        if (count($querystring)) {
            $place .= '&' . http_build_query($querystring);
        }
        header('location: ?q=' . $place);
        exit;
    }

    public function removeDirectoryTraversal($path)
    {
        return trim(str_replace('..', '', $path), '/');
    }

    public function verifyClassExists($classname)
    {
        if (!file_exists('modules/' . $classname . '/controller/' . $classname . '.php')) {
            echo "No such controller";
            exit;
        }
    }

    protected function getString($name, $default_value = '')
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default_value;
    }

    protected function getPost($name, $default_value = '')
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default_value;
    }

    protected function toArray($javascriptarray, $explodeval = '.', $arrayval = '')
    {
        $newarray = array();
        foreach ($javascriptarray as $partarray) {
            foreach ($partarray as $key => $value) {
                $parts = explode($explodeval, $key);

                $array = $this->iterateToArray($parts, $value, $arrayval);
                $newarray = array_merge_recursive($array, $newarray);
            }
        }

        $endarray = $this->stupidIterateMakeNormalArray($newarray);

        return $endarray;
    }

    protected function stupidIterateMakeNormalArray($newarray)
    {
        if (is_array($newarray)) {
            $newarray = array_reverse($newarray);
            $endarray = array();

            foreach ($newarray as $key => $value) {
                if (substr($key, 0, 6) == 'array_') {
                    $key = (int) substr($key, 6);
                }
                $endarray[$key] = $this->stupidIterateMakeNormalArray($value);
            }
        } else {
            $endarray = $newarray;
        }

        return $endarray;
    }

    protected function iterateToArray($array, $value, $arrayval)
    {
        $i = 0;
        $make_array = false;

        foreach ($array as $string) {
            if (!$i) {
                if ($arrayval) {
                    if (substr($string, 0, strlen($arrayval)) == $arrayval) {
                        $make_array = true;
                        $newstring = substr($string, strlen($arrayval));
                        $parts = explode('_', $newstring);
                        $nr = (int) $parts[0];
                        unset($parts[0]);
                        $string = implode('_', $parts);
                    }
                }

                array_shift($array);
                if (count($array)) {
                    if ($make_array) {
                        $output['array_' . $nr][$string] = $this->iterateToArray($array, $value, $arrayval);
                    } else {
                        $output[$string] = $this->iterateToArray($array, $value, $arrayval);
                    }

                } else {
                    if ($make_array) {
                        if ($string) {
                            $output[$nr][$string] = $value;

                        } else {
                            $output[$nr] = $value;
                        }
                    } else {
                        $output[$string] = $value;
                    }
                }
            }
            $i++;
        }

        return $output;
    }

    protected function getValueFields($properties, &$array = array(), $level = 0, $keyname = '')
    {
        $nested = array();

        if (isset($properties['properties'])) {
            foreach ($properties['properties'] as $name => $values) {
                if (isset($values['properties']) || $values['type'] == 'nested' || $values['type'] == 'object') {
                    $array[$name]['name'] = $name;
                    $this->getValueFields($values, $array, 1, $name);
                } elseif ($values['type'] == 'multi_field') {
                    foreach ($values['fields'] as $mfkey => $mfvalue) {
                        $array[$name]['fields'][] = array(
                            'name' => $name == $mfkey ? '' : $mfkey,
                            'type' => $mfvalue['type']
                        );
                    }
                } else {
                    $array[$keyname]['fields'][] = array('name' => $name, 'type' => $values['type']);
                }
                if (!$level) {
                    $prefix = '';
                    foreach ($array as $key => $value) {
                        $prefix .= $key . '.';
                        if (isset($value['fields'])) {
                            foreach ($value['fields'] as $field) {
                                $nested[$field['type']][] = $prefix . $field['name'];
                            }
                        }

                    }
                    unset($array);
                }
            }
        }

        return $nested;
    }

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
