<?php

/**
 * Url takes care of creations of link
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class Url
{
    /**
     * Creation of a link
	 * 
     * @param string $url The url to visit
     * @param string $html The link text/html
	 * @param array $vars Extra variables to append to the link
     */	
    public function createUrl($url, $html, $vars = array())
    {
        $vars['class'] = isset($vars['class']) ? $vars['class'] : '';

        return '<a href="?q=' . $url . '" class="' . $vars['class'] . '">' . $html . '</a>';
    }
}
