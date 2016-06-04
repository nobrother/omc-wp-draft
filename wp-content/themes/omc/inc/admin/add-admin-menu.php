<?php
/**
 * Add admin menu hook
 */

add_action( 'after_setup_theme', 'omc_add_admin_menu' );
/**
 * Add OMC top-level item in admin menu.
 */
function omc_add_admin_menu() {

	require_once OMC_ADMIN_CLASS_DIR . '/class-omc-admin-theme-settings.php';
	require_once OMC_ADMIN_CLASS_DIR . '/class-omc-admin-css-editor.php';
	require_once OMC_ADMIN_CLASS_DIR . '/class-omc-admin-js-editor.php';
	require_once OMC_ADMIN_CLASS_DIR . '/class-omc-admin-template-editor.php';
	
	// Add admin menu hook
	do_action( 'omc_admin_menu' );

}