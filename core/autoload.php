<?php
function ESManagerAutoLoad($class_name) {
	if(file_exists('helper/' . $class_name . '.php'))
	{
    	require_once('helper/' . $class_name . '.php');
	}
	else
	{
		$dirs = scandir('modules');
		foreach($dirs as $dir)
		{
			if(substr($dir, 0, 1) != '.')
			{
				if(file_exists('modules/' . $dir . '/helper/' . $class_name . '.php'))
				{
					require_once('modules/' . $dir . '/helper/' . $class_name . '.php');
				}
			}
		}
	}
}

spl_autoload_register('ESManagerAutoLoad');
?>