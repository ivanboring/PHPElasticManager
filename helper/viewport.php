<?php

/**
 * Viewport takes care of all outputing and templating
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class Viewport
{
	/**
     * The output
     *
     * @var output
     */	
    private $output = '';

    /**
     * Render a template
	 * 
     * @param string $view_name The template to use
     * @param array $vars Variables to use in the template
     * @param bool $return Should the rendered template be outputted or returned
	 * @param string $calling_class Controller used for the template
	 * 
     * @return string If $return is equal to true it will return the rendered template
     */
    public function render($view_name, $vars = array(), $return = true, $calling_class = '')
    {
        ob_start();

        if ($calling_class) {
            require 'modules/'. $calling_class . '/view/' . $view_name . '.tpl.php';
        } else {
            require 'view/'. $view_name . '.tpl.php';
        }

        if ($return) {
            $output = ob_get_contents();
        } else {
            $this->output .= ob_get_contents();
        }

        ob_end_clean();

        if ($return) { return $output; }
    }

    /**
     * Writes out the output variable
     */
    public function createPage()
    {
        echo $this->output;
    }

    /**
     * Verifies that the template exists
	 * 
	 * @param string $view_name The name of the template
     */
    public function verifyTemplate($view_name)
    {
        if (!file_exists('view/' . $view_name . '.tpl.php')) {
            echo "No such controller";
            exit;
        }
    }

}
