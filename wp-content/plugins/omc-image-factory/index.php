<?php

// Load config
require_once( 'config.php' );

// Security check
if( !isset( $_GET['hash'] ) )
	die();
	
$url = 'http'.( isset( $_SERVER['HTTPS'] ) ? 's' : '' ).'://'."{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$hash = $_GET['hash'];
$url = substr( $url, 0, -strlen( '&hash='.$hash ) ); // Remove hash part
$url = urldecode( $url ); // Decode

if( !omc_check_image_hash( $url, $hash ) ){
	die();
}

// Gen image
require_once( OMC_IMAGE_FACTORY_PHPTHUMB_DIR.'phpThumb.php' );