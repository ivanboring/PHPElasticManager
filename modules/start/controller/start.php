<?php

/**
 * Start page
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class controllerStart extends router
{
    /**
     * Create the start page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */		
    public function page_index($args)
    {
        $vars['content'] = $this->renderPart('start');
        $vars['title'] = 'Start';

        return $vars;
    }
}
