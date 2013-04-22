<?php

class url
{
	public function __construct() {

	}
	
	public function createUrl($url, $html, $vars = array())
	{
		$vars['class'] = isset($vars['class']) ? $vars['class'] : '';
		
		return '<a href="?q=' . $url . '" class="' . $vars['class'] . '">' . $html . '</a>';
	}
}

?>