<?php
namespace OMC\User;
use \WP_Exception as e;
ignore_user_abort(true);

define( 'DOING_API', true );
require_once( '../../wp-load.php' );

$data = array(
	'user_login' => 'abc',
	'user_email' => 'abc@abc.com',
	'user_pass' => 'sadkldkfjajf',
);
Main::register($data);
die();