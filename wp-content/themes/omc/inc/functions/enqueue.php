<?php
/**
 * Enqueue script helper functions
 * Internal used, don't use directly
 */

function omc_enqueue( $handler, $ext, $filename, $path, $depandancy = array(), $version = false, $in_footer = true ){

	$path = wp_normalize_path( $path.'/' );
	$min_path = $path.'min/';
	$url_path = get_current_url_path( $path );
	
	// Change less to css if any
	if( $ext == 'less' )
		$ext = 'css';
	
	// Get file location
	$file = locate_file( array(	$min_path.$filename.'.min', $path.$filename ), '', $ext );
	if( empty( $file ) )
		return false;
	
	// Test if the file exists
	$fullpath = $file['full'];
	if( !file_exists( $fullpath ) )
			return false;
	
	$url = home_url( $file['home_path'] );
	
	switch( $ext ){
		case 'css': wp_enqueue_style( $handler, $url, $depandancy, $version, 'all' );	break;
		case 'js': wp_enqueue_script( $handler, $url, $depandancy, $version, $in_footer ); break;
	}
}

