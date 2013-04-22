<?php 

class controllerStart extends router
{
	public function __construct() {

	}
	
	public function page_index($args)
	{
		$vars['content'] = $this->renderPart('start');
		$vars['title'] = 'Start';
		return $vars;
	}
}

?>