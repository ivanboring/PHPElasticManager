<?php

/**
 * About page
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class controllerAbout extends router
{
    /**
     * Renders an about page
	 * 
     * @param array $args Page arguments
	 * 
     * @return array Variables to render a page
     */	
    public function page_index($args)
    {
        $vars['content'] = $this->renderPart('about', $arguments);
        $vars['title'] = 'About PHPElasticManager';

        return $vars;
    }

    /**
     * Adds a menu item
	 * 
     * @return array Menu item array
     */	
    public function menu_items()
    {
        return array(
            'path' => 'about',
            'title' => 'About',
            'weight' => 10
        );
    }

}
