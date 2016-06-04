<?php

defined( 'ABSPATH' ) or die( "Don't try to do silly things!" );

global $post;

switch( $post->post_type ){
	case 'post':
		require_once 'post/layout.php';
	break;
	
	default:
		require_once $post->post_type.'/'.$post->post_name.'/layout.php';
	
}
