<?php
/*
Plugin Name: omc image factory
Description: Resize, Crop, Filter image with url only. Using phpThumb
Version: 1.0.0
Author: Chang
Author URI: http://ohmycode.com.my/
Date: 07/08/2015
*/

// Load config
require_once( 'config.php' );

define( 'OMC_IMAGE_FACTORY_URL', plugin_dir_url( __FILE__ ) );
define( 'OMC_IMAGE_FACTORY_CACHE_URL', OMC_IMAGE_FACTORY_URL.'cache/' );

function _omc_image_url( $id, $args = array() ){
	
	if( empty( $id ) )
		return false;
		
	if( !( $url = wp_get_attachment_url( $id ) ) )
		return false;
		
	if( empty( $args ) )
		return $url;
	
	// src
	//$args['src'] = str_ireplace( '\\', '/', substr( get_attached_file( $id ), strlen( $_SERVER['DOCUMENT_ROOT'] ) - 1 ) );
	$args['src'] = get_attached_file( $id );
	
	// Format
	$file_type = wp_check_filetype( $args['src'] );
	switch( $file_type['type'] ){
		case 'image/png': $args['f'] = 'png'; break;
		case 'image/gif': $args['f'] = 'gif'; break;
		default: $args['f'] = 'jpeg';
		
	}
	
	$url = add_query_arg( $args, OMC_IMAGE_FACTORY_URL );
	
	// hash
	$hash = omc_gen_image_hash( urldecode( $url ) );
	
	return add_query_arg( 'hash', $hash, $url );
	
}

// Helper function: General use
function omc_image_url( $id, $width = false, $height = false, $crop = false, $filter = '', $extra = array() ){
	
	global $_omc_image_factory_crop_position;
	global $_omc_image_factory_filter;
	
	$args = array();
	
	// Width
	if( !empty( $width ) && is_int( $width ) )
		$args['w'] = $width;
		
	// Height
	if( !empty( $height ) && is_int( $height ) )
		$args['h'] = $height;
	
	// Zoom crop
	$crop_position = 'center';	// Only center will work
	if( !empty( $crop ) && isset( $args['w'] ) && isset( $args['h'] ) ){
		if( !isset( $_omc_image_factory_crop_position[$crop_position] ) )
			$args['zc'] = 'C';
		else
			$args['zc'] = $_omc_image_factory_crop_position[$crop_position];
	}
	
	/* 
	 * Filter
	 */
	if( !empty( $filter ) && isset( $_omc_image_factory_filter[ $filter ] ) )
		$args['fltr'] = $_omc_image_factory_filter[ $filter ]; 
	 
	// Extra
	if( !empty( $extra ) && is_array( $extra ) )
		$args = wp_parse_args( $args, $extra );
	
	return _omc_image_url( $id, $args );
}
