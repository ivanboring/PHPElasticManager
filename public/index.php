<?php 

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Change working directory outside public
chdir('../');
require_once('config.php');
require_once('core/router.php');

$router = new router($config);


?>