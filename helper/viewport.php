<?php 

class viewPort
{
	static $output = '';
	public function __construct() {

	}
	
	public function render($view_name, $vars = array(), $return = true, $calling_class = '')
	{
		ob_start();
		
		if($calling_class)
		{
			require('modules/' . $calling_class . '/view/' . $view_name . '.tpl.php');	
		}
		else
		{
			require('view/' . $view_name . '.tpl.php');			
		}
	
		if($return)
		{			
			$output = ob_get_contents();			
		}
		else
		{			
			self::$output .= ob_get_contents();
		}
		
		ob_end_clean();
		
		if($return) { return $output; }
	}
	
	public function createPage()
	{
		echo self::$output;
	}
	
	public function verifyTemplate($view_name)
	{
		if(!file_exists('view/' . $view_name . '.tpl.php'))
		{
			
		}
	}
	
}

?>