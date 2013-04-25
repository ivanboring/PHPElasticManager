<?php

class controllerAbout extends router
{
    public function __construct()
    {
    }

    public function page_index($args)
    {
        $vars['content'] = $this->renderPart('about', $arguments);
        $vars['title'] = 'About PHPElasticManager';

        return $vars;
    }

    public function menu_items()
    {
        return array(
            'path' => 'about',
            'title' => 'About',
            'weight' => 10
        );
    }

}
