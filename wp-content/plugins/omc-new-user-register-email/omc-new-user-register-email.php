<?php

/*
Plugin Name: omc new user notification
Description: Better function for wp_new_user_notification(). Add useful hook to override email, subject, message
Version: 1.0.0
Author: Chang
*/

defined( 'ABSPATH' ) or die();

if ( !function_exists('wp_new_user_notification') ) :

// Set Flag
define( 'OMC_WP_NEW_USER_NOTIFICATION', true );

/**
 * Email login credentials to a newly-registered user.
 *
 * A new user registration notification is also sent to admin email.
 *
 * @since 2.0.0
 *
 * @param int    $user_id        User ID.
 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
 */
function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
	
	// Short circuit hook
	if( !apply_filters( 'omc_pre_new_user_notification', false, $user_id, $plaintext_pass ) )
		return false;
	
	// Filter user
	$user = apply_filters( 'omc_new_user_notification_get_user', get_userdata( $user_id ), $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user->user_email) . "\r\n";
	$message .= "IP: {$_SERVER['REMOTE_ADDR']}" . "\r\n";
	
	// Filter admin message
	$message = apply_filters( 'omc_new_user_notification_admin_message', $message, $user, $blogname );
	
	// Filter admin email
	$admin_email = apply_filters( 'omc_new_user_notification_admin_email', get_option('admin_email') );
	
	// Filter admin email subject
	$admin_email_subject = apply_filters( 'omc_new_user_notification_admin_email_subject', sprintf( __('[%s] New User Registration'), $blogname ), $user, $blogname );
	
	// Sent admin email
	@wp_mail( $admin_email, $admin_email_subject, $message);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
	$message .= wp_login_url() . "\r\n";
	
	// Filter user message
	$message = apply_filters( 'omc_new_user_notification_user_message', $message, $user, $blogname );
	
	// Filter user email
	$user_email = apply_filters( 'omc_new_user_notification_user_email', $user->user_email );
	
	// Filter user email subject
	$user_email_subject = apply_filters( 'omc_new_user_notification_user_email_subject', sprintf( __('[%s] New User Registration'), $blogname ), $user, $blogname );
	
	wp_mail( $user_email, $user_email_subject, $message);

}
endif;