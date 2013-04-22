<?php 

/*
 * Configuration file for Elastic Admin. Please fill in the things you need.
 */

/*
 * The host name for elasticsearch server
 */
$config['servers']['host'] = 'http://localhost';

/*
 * The port for the elasticsearch server
 */
$config['servers']['port'] = '9200';


/*
 * The users that are allowed in. Format 'username' => 'password'
 */
$config['users'] = array(
	'test' => 'arneweise'
);
?>