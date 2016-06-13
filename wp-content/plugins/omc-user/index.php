<?php

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Exit if accessed directly
}

/**
 * Plugin Name: omc user
 * Plugin URI: http://ohmycode.com.my/
 * Description: This plugin restricts the access to the admin panel and provides register, profile, login and logout features inside of the theme.
 * Version: 1.0.0
 * Author: Chang
 */
 
// Define dir contants
define( 'OMC_USER_DIR', dirname( __FILE__ ) );
define( 'OMC_USER_INC_DIR', OMC_USER_DIR.'/inc' );
define( 'OMC_USER_CLASS_DIR', OMC_USER_INC_DIR.'/class' );
define( 'OMC_USER_TEMPLATE_DIR', OMC_USER_DIR . '/templates' );

// Define url constants
define( 'OMC_USER_URL', plugin_dir_url( __FILE__ ) );
define( 'OMC_USER_JS_URL', OMC_USER_URL . '/assets/js' );

require_once OMC_USER_CLASS_DIR.'/class-omc-user.php';
new OMC_User();	// Initiate main class