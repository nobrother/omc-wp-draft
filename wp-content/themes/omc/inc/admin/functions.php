<?php
/**
 * OMC general functions
 */
 /**
 * Check that we're targeting a specific admin page.
 *
 * @param string $pagehook Page hook string to check.
 * @return boolean Return true if the global $page_hook matches given $pagehook. False otherwise.
 */
function is_menu_page( $pagehook = '' ) {
	global $page_hook;
	if ( isset( $page_hook ) && $page_hook === $pagehook )
		return true;
	//* May be too early for $page_hook
	if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $pagehook )
		return true;
	return false;
}

/**
 * Redirect the user to an admin page, and add query args to the URL string for alerts, etc.
 * * @param string $page       Menu slug. * @param array  $query_args Optional. Associative array of query string arguments (key => value). Default is an empty array. * * @return null Return early if first argument is falsy. */
function omc_admin_redirect( $page, array $query_args = array() ) {
	if ( ! $page )		
		return;
	$url = html_entity_decode( menu_page_url( $page, 0 ) );
	foreach ( (array) $query_args as $key => $value ) {		
		if ( empty( $key ) && empty( $value ) ) {			
			unset( $query_args[$key] );		
		}	
	}
	$url = add_query_arg( $query_args, $url );
	wp_redirect( esc_url_raw( $url ) );
}