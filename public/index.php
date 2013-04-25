<?php

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
session_start();

// Change working directory outside public
chdir('../');

// Get configuration
require_once 'config.php';

// Start the autoloading
require_once 'core/autoload.php';

// Make the l a global function so we don't have to think about objects in the view files
function l($link, $html, $vars = array())
{
    $url = new url;

    return $url->createUrl($link, $html, $vars);
}

// Start the show
require_once 'core/router.php';
$router = new Router();
$router->route($config);
