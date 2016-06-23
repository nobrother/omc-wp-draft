<?php
namespace OMC\User;
use \WP_Exception as e;
ignore_user_abort(true);

define( 'DOING_API', true );
require_once( '../../wp-load.php' );

$data = array(
	'user_email' => "chang@ohmycode.coma",
	'user_login' => "asdasd",
	'user_pass' => "sasfasfasf"
);

$result = Main::register( $data );
var_dump($data);
die();