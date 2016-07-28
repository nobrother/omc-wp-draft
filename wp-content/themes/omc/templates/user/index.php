<?php
namespace OMC\User;

global $current_user, $this_user;

// Load current user object
if( is_user_logged_in() )
	$this_user = new Object( $current_user );	


// Requirement
switch( Main::current_user_page() ){
	// Require login
	case 'user_change_password':
	case 'user_edit_profile':
	if( !is_user_logged_in() )
		wp_die( 'How do you get here?! You need to login first!' );
	break;
}

// Choose page
switch( Main::current_user_page() ){
	case 'user_login': include 'login/layout.php'; break;	
	case 'user_register':	include 'register/layout.php'; break;	
	case 'user_edit_profile': 	include 'account/edit/layout.php'; break;
	case 'user_change_password': 	include 'account/password/layout.php'; break;
	case 'user_logout': 	include 'logout/layout.php'; break;
}