<?php 

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Exit if accessed directly
}

/**
 * OMC User Action
 *
 * Action class for the plugin
 *
 * @class 		OMC_User
 * @version		1.0.0
 * @plugin		omc-user
 * @category	Class
 * @author 		Chang
 */ 
 
class OMC_User_Action {
	
	/**
	 * Construct
	 */
	public function __construct() {
		
		/**
		 * Inits the plugins, loads all the files
		 * and registers all actions and filters
		 */

		// the action handler
		add_action( 'init', array( $this, 'action_handler' ) );

		// the special error handler
		add_action( 'omc_user_error_messages', array( $this, 'error_messages' ) );

		// user activation
		add_action( 'omc_user_activation', array( $this, 'perform_activation' ) );
		add_action( 'omc_user_activation_messages', array( $this, 'activation_messages' ) );

		// forgot password
		add_action( 'omc_user_forgot_password', array( $this, 'perform_forgot_password' ) );
		add_action( 'omc_user_forgot_password_messages', array( $this, 'forgot_password_messages' ) );

		// user login
		add_action( 'omc_user_login', array( $this, 'perform_login' ) );
		add_action( 'omc_user_login_messages', array( $this, 'login_messages' ) );

		// user logout
		add_action( 'omc_user_logout', array( $this, 'perform_logout' ) );

		// user profile
		add_action( 'omc_user_profile', array( $this, 'perform_profile_edit' ) );
		add_action( 'omc_user_profile_messages', array( $this, 'profile_messages' ) );

		// registration
		add_action( 'omc_user_register', array( $this, 'perform_register_edit' ) );
		add_action( 'wpmu_signup_user_notification', array( $this, 'wpmu_signup_user_notification' ), 1, 4 );
		add_action( 'omc_user_register_messages', array( $this, 'register_messages' ) );

		// reset password
		add_action( 'omc_user_reset_password', array( $this, 'perform_reset_password' ) );
		add_action( 'omc_user_reset_password_messages', array( $this, 'reset_password_messages' ) );
	}
	
	/**
	 * Handles all the incoming actions
	 */
	public function action_handler(){

		// checking the action
		if ( ! isset( $_REQUEST[ 'action' ] ) || ! has_action( 'omc_user_' .$_REQUEST[ 'action' ] ) ) {
			// check if we need to do something here
			if ( strstr( $_SERVER[ 'REQUEST_URI' ], '/'.OMC_User::$page_slugs['action'].'/' ) ) {
				wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['error'].'/?message=noaction' ) );
				exit;
			} else {
				return FALSE;
			}
		}

		// checking the nonce
		$nonce_request_key = '_wpnonce';
		if ( ! isset( $_REQUEST[ $nonce_request_key ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_request_key ], $_REQUEST[ 'action' ] ) ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['error'].'/?message=nononce' ) );
			exit;
		}

		do_action( 'omc_user_set_request_vars' );
		do_action( 'omc_user_' . $_REQUEST[ 'action' ] );
		
		return true;
	}
	
	/**
	 * Displays error message
	 */
	public function error_messages( $message ) {

		switch ( $message ) {
			case 'noaction':
				?><div class="error"><p><?php echo 'No action was setted.' ?></p></div><?php
				break;
			case 'nononce':
			default:
				?><div class="error"><p><?php echo 'Are you sure you are supposed to do this?' ?></p></div><?php
				break;
		}
	}
	
	/**
	 * Activates a user if it is necessary
	 */
	function perform_activation() {

		if ( ! isset( $_POST[ 'user_key' ] ) ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['activation'].'/?message=keynotvalid' ) );
			exit;
		} else {
			$key = $_POST[ 'user_key' ];
			$result = wpmu_activate_signup( $key );
			if ( is_wp_error( $result ) ) {
				wp_safe_redirect(  home_url( '/'.OMC_User::$page_slugs['activation'].'/?message=keynotvalid' ) );
				exit;
			} else {

				$user = get_userdata( $result[ 'user_id' ] );
				$username = $user->user_login;
				$password = $result[ 'password' ];

				// set credentials
				$credentials = array(
					'user_login'	=> $username,
					'user_password'	=> $password,
				);

				// signon user
				$user = wp_signon( $credentials );

				wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['profile'].'/?message=activated' ) );
				exit;
			}
		}
	}

	/**
	 * Displays activation message
	 */
	public function activation_messages( $message ) {
		switch ( $message ) {
			case 'keynotvalid':
				?><div class="error"><p><?php echo 'The key is not valid.' ?></p></div><?php
				break;
			default:
				break;
		}
	}
	
	/**
	 * Checks if the forgot password call is valid
	 */
	public function perform_forgot_password() {

		$errors = $this->retrieve_password();
		if ( ! is_wp_error( $errors ) ) {
			wp_safe_redirect(  home_url( '/'.OMC_User::$page_slugs['forgotpassword'].'/?message=confirmationsend' ) );
			exit();
		} else {
			wp_safe_redirect(  home_url( '/'.OMC_User::$page_slugs['forgotpassword'].'/?message=' . $errors->get_error_code() ) );
			exit();
		}
	}

	/**
	 * Retrives the password for a requested login
	 *
	 * @return WP_Error|mixed|boolean
	 */
	public function retrieve_password() {
		global $wpdb, $current_site;

		$errors = new WP_Error();

		if ( empty( $_POST[ 'user_login' ] ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.' ) );
		} else if ( strpos( $_POST[ 'user_login' ], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
			if ( empty( $user_data ) )
				$errors->add( 'invalid_email', '<strong>ERROR</strong>: There is no user registered with that email address.' );
		} else {
			$login = trim( $_POST[ 'user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		do_action( 'lostpassword_post' );

		if ( $errors->get_error_code() )
			return $errors;

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', '<strong>ERROR</strong>: Invalid username or e-mail.' );
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retreive_password', $user_login );  // Misspelled and deprecated
		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', TRUE, $user_data->ID );

		if ( ! $allow )
			return new WP_Error( 'no_password_reset', 'Password reset is not allowed for this user' );
		else if ( is_wp_error( $allow ) )
			return $allow;

		$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
		if ( empty( $key ) ) {
			// Generate something random for a key...
			$key = wp_generate_password( 20, FALSE );
			do_action( 'retrieve_password_key', $user_login, $key );
			// Now insert the new md5 key into the db
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
		}
		$message = 'Someone requested that the password be reset for the following account:' . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( 'Username: %s', $user_login ) . "\r\n\r\n";
		$message .= 'If this was a mistake, just ignore this email and nothing will happen.' . "\r\n\r\n";
		$message .= 'To reset your password, visit the following address:' . "\r\n\r\n";
		$message .= '<' . get_bloginfo( 'url' ) . '/'.OMC_User::$page_slugs['resetpassword'].'/?key=' . $key . '&login=' . rawurlencode( $user_login ) . ">\r\n";

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		if ( is_multisite() )
			$blogname = $GLOBALS[ 'current_site' ]->site_name;
		else
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$title = sprintf( '[%s] Password Reset', $blogname );

		$title = apply_filters( 'retrieve_password_title', $title );
		$message = apply_filters( 'retrieve_password_message', $message, $key );

		if ( $message && ! wp_mail( $user_email, $title, $message ) )
			wp_die( 'The e-mail could not be sent.' . "<br />\n" .'Possible reason: your host may have disabled the mail() function...' );

		return true;
	}

	/**
	 * Displays forgot password message
	 */
	public function forgot_password_messages( $message ) {
		switch ( $message ) {
			case 'confirmationsend':
				?><div class="updated"><p><?php echo 'Check your e-mail for the confirmation link.' ?></p></div><?php
				break;
			case 'empty_username':
				?><div class="error"><p><?php echo 'Please enter something.' ?></p></div><?php
				break;
			case 'invalid_email':
				?><div class="error"><p><?php echo'The E-Mail address is not valid.' ?></p></div><?php
				break;
			case 'invalidcombo':
				?><div class="error"><p><?php echo 'Your input doesn\'t match anything.' ?></p></div><?php
				break;
			case 'no_password_reset':
				?><div class="error"><p><?php echo 'This action is not allowed.' ?></p></div><?php
				break;
			default:
				break;
		}
	}
	
	/**
	 * Checks the credentials and performs the login
	 */
	public function perform_login() {
		
		// check username
		if ( ! isset( $_POST[ 'user_login' ] ) || trim( $_POST[ 'user_login' ] ) == '' ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/?message=nodata' ) );
			exit;
		}

		// check password
		if ( ! isset( $_POST[ 'user_pass' ] ) || trim( $_POST[ 'user_pass' ] ) == '' ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/?message=nodata' ) );
			exit;
		}
		
		// set credentials
		$credentials = array(
			'user_login'	=> $_POST[ 'user_login' ],
			'user_password'	=> $_POST[ 'user_pass' ],
		);

		// remember me
		if ( isset( $_POST[ 'rememberme' ] ) && $_POST[ 'rememberme' ] == 'on' )
			$credentials[ 'remember' ] = 'on';

		// signon user
		$user = wp_signon( $credentials );

		// check login
		if ( ! is_wp_error( $user ) ) {

			// set the url
			if ( isset( $_POST[ 'redirect_to' ] ) && trim( $_POST[ 'redirect_to' ] ) != '' )
				$url = $_POST[ 'redirect_to' ];
			else
				$url = home_url( '/'.OMC_User::$page_slugs['profile'].'/' );
			
			$url = apply_filters( 'uf_perform_login_redirection_url', $url );
			wp_safe_redirect( $url );
			
			exit;
		} else {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/?message=nologin' ) );
			exit;
		}
	}

	/**
	 * Displays login message
	 */
	public function login_messages( $message ) {

		switch ( $message ) {
			case 'nodata':
				?><div class="error"><p><?php echo 'We need some input values to handle the login.' ?></p></div><?php
				break;
			case 'nologin':
				?><div class="error"><p><?php echo 'Username or password is incorrect.' ?></p></div><?php
				break;
			case 'password_resetted':
				?><div class="updated"><p><?php echo 'Password has been resetted.' ?></p></div><?php
				break;
			case 'registered':
				?><div class="updated"><p><?php echo 'You have been successfully registered. Please check your mail for your credentials and more informations.' ?></p></div><?php
				break;
			case 'regdisabled':
				?><div class="error"><p><?php echo 'Registration is currently disabled.' ?></p></div><?php
				break;
			case 'loggedout':
				?><div class="updated"><p><?php echo 'You have been successfully logged out.' ?></p></div><?php
				break;
			case 'wpduact_inactive':
				?><div class="updated"><p><?php echo 'Your account has been temporarily deactivated.' ?></p></div><?php
				break;
			default:
				break;
		}
	}
	
	/**
	 * Loads the current user out
	 */
	public function perform_logout() {
		wp_logout();
		$url_after_logout = apply_filters( 'uf_perform_logout_url', '/'.OMC_User::$page_slugs['login'].'/?message=loggedout' );
		wp_safe_redirect( home_url( $url_after_logout ) );
		exit;
	}
	
	/**
	 * Edits the user
	 */
	public function perform_profile_edit() {

		// get user id
		$user_id = get_current_user_id();

		// perform profile actions for plugins
		do_action( 'personal_options_update', $user_id );

		// edit user
		if ( ! function_exists( 'edit_user' ) )
			require_once ABSPATH . '/wp-admin/includes/user.php';
		$errors = edit_user( $user_id );

		// check for errors (mainly password)
		if ( ! is_wp_error( $errors ) ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['profile'].'/?message=updated' ) );
			exit;
		} else {
			$error_code = $errors->get_error_code();
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['profile'].'/?message=' . $error_code ) );
			exit;
		}
	}

	/**
	 * Displays profile message
	 */
	public function profile_messages( $message ) {
		switch ( $message ) {
			case 'updated':
				?><div class="updated"><p><?php echo 'Profile has been updated.' ?></p></div><?php
				break;
			case 'pass':
				?><div class="error"><p><?php echo 'The passwords mismatch.' ?></p></div><?php
				break;
			case 'invalid_email':
				?><div class="error"><p><?php echo 'E-Mail address is not valid.' ?></p></div><?php
				break;
			case 'empty_email':
				?><div class="error"><p><?php echo 'Please enter an E-Mail address.' ?></p></div><?php
				break;
			case 'email_exists':
				?><div class="error"><p><?php echo 'This email is already registered, please choose another one.' ?></p></div><?php
				break;
			case 'activated':
				?><div class="updated"><p><?php echo 'Your account has been activated. Now you can edit your profile.' ?></p></div><?php
				break;
			default:
				break;
		}
	}
	
	/**
	 * Performs the register of a user
	 */
	public function perform_register_edit() {

		if ( is_multisite() )
			$this->register_multisite_user();
		else
			$this->register_standard_user();
	}

	/**
	 * Registers the user in a multisite setup
	 * called at uf_perform_register_edit()
	 */
	public function register_multisite_user() {
		$this->validate_user_signup();
	}

	/**
	 * Validates the user input and registers the
	 * user, called at uf_perform_register_edit()
	 */
	public function validate_user_signup() {

		// set user mail
		$_POST[ 'user_name' ] = $_POST[ 'user_login' ];
		$_POST[ 'user_email' ] = $_POST[ 'user_email' ];

		// validate
		$result = $this->validate_user_form();
		extract( $result );

		if ( $errors->get_error_code() ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['register'].'/?message=' . $errors->get_error_code() ) );
			exit;
		} else {
			$_POST[ 'add_to_blog' ] = get_current_blog_id();
			$_POST[ 'new_role' ] = 'subscriber';
			wpmu_signup_user( $user_name, $user_email, apply_filters( 'add_signup_meta', $_POST ) );
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/?message=registered' ) );
			exit;
		}
	}

	/**
	 * Validates the user input
	 */
	public function validate_user_form() {
		return wpmu_validate_user_signup( $_POST[ 'user_name' ], $_POST[ 'user_email' ] );
	}

	/**
	 * Validates the user input and registers the
	 * user, called at uf_perform_register_edit()
	 */
	public function register_standard_user() {

		// set data
		$user_login = $_POST[ 'user_login' ];
		$user_email = $_POST[ 'user_email' ];
		$user_pass = isset( $_POST[ 'user_pass' ] ) ? $_POST[ 'user_pass' ] : '';

		// register the user
		$errors = $this->register_new_user( $user_login, $user_email, $user_pass );
		if ( ! is_wp_error( $errors ) ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/?message=registered' ) );
			exit;
		} else {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['register'].'/?message=' . $errors->get_error_code() ) );
			exit;
		}
	}

	/**
	 * Registers the new user
	 */
	public function register_new_user( $user_login, $user_email, $user_pass = '' ) {
		$errors = new WP_Error();

		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', '<strong>ERROR</strong>: Please enter a username.' );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', '<strong>ERROR</strong>: This username is already registered. Please choose another one.' );
		}

		// Check the e-mail address
		if ( $user_email == '' ) {
			$errors->add( 'empty_email', '<strong>ERROR</strong>: Please type your e-mail address.' );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', '<strong>ERROR</strong>: The email address isn&#8217;t correct.' );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', '<strong>ERROR</strong>: This email is already registered, please choose another one.' );
		}

		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() )
			return $errors;

		$user_pass = empty( $user_pass ) ? wp_generate_password( 12, false ) : $user_pass;
		$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
		if ( ! $user_id ) {
			$errors->add( 'registerfail', sprintf(  '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', get_option( 'admin_email' ) ) );
			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

		wp_new_user_notification( $user_id, $user_pass );

		return $user_id;
	}

	/**
	 * Sends the notification
	 */
	public function wpmu_signup_user_notification( $user, $user_email, $key, $meta ) {

		// Send email with activation link.
		$admin_email = get_site_option( 'admin_email' );
		if ( $admin_email == '' )
			$admin_email = 'support@' . $_SERVER['SERVER_NAME'];

		$from_name = get_site_option( 'site_name' ) == '' ? 'WordPress' : esc_html( get_site_option( 'site_name' ) );
		$message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
		$message = sprintf(
			apply_filters( 'wpmu_signup_user_notification_email',
				__( "To activate your user, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login." ),
				$user, $user_email, $key, $meta
			),
			home_url( '/'.OMC_User::$page_slugs['activation'].'/?key=' . $key )
		);

		$subject = sprintf(
			apply_filters( 'wpmu_signup_user_notification_subject',
				__( '[%1$s] Activate %2$s' ),
				$user, $user_email, $key, $meta
			),
			$from_name,
			$user
		);

		wp_mail( $user_email, $subject, $message, $message_headers );
	}

	/**
	 * Displays a message
	 */
	public function register_messages( $message ) {

		switch ( $message ) {
			case 'user_name':
			case 'empty_username':
			case 'invalid_username':
			case 'illegal_names':
				?><div class="error"><p><?php echo 'Invalid Username.' ?></p></div><?php
				break;
			case 'username_exists':
				?><div class="error"><p><?php echo 'This username exists.' ?></p></div><?php
				break;
			case 'empty_email':
			case 'user_email':
			case 'invalid_email':
				?><div class="error"><p><?php echo 'Invalid E-Mail address.' ?></p></div><?php
				break;
			case 'email_exists':
			case 'user_email_used':
				?><div class="error"><p><?php echo 'E-Mail exists.' ?></p></div><?php
				break;
			case 'registerfail':
				?><div class="error"><p><?php echo 'Something went wrong. Please consult the administrator.' ?></p></div><?php
				break;
			default:
				break;
		}
	}
	
	/**
	 * Performs the reset password action
	 */
	public function perform_reset_password() {

		// get user
		$user = $this->check_password_reset_key( $_POST[ 'user_key' ], $_POST[ 'user_login' ] );

		// check for key
		if ( is_wp_error( $user ) ) {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['resetpassword'].'/?message=invalid_key' ) );
			exit;
		}

		// check password
		$errors = new WP_Error();
		if ( isset( $_POST[ 'pass1' ] ) && $_POST[ 'pass1' ] != $_POST[ 'pass2' ] )
			$errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );

		// action for plugins
		do_action( 'validate_password_reset', $errors, $user );

		// set action
		if ( ( ! $errors->get_error_code() ) && isset( $_POST['pass1'] ) && !empty( $_POST['pass1'] ) ) {
			$this->reset_password( $user, $_POST[ 'pass1' ] );
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/?message=password_resetted' ) );
			exit;
		} else {
			wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['resetpassword'].'/?message=validate_password_reset' ) );
			exit;
		}
	}

	/**
	 * reset password
	 */
	public function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );
		wp_set_password( $new_pass, $user->ID );
		wp_password_change_notification( $user );
	}

	/**
	 * check password key
	 */
	public function check_password_reset_key( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || !is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( empty( $login ) || !is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );

		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		return $user;
	}

	/**
	 * Displays a message
	 */
	public function reset_password_messages( $message ) {
		switch ( $message ) {
			case 'invalid_key':
				?><div class="error"><p><?php echo 'Invalid Key.' ?></p></div><?php
				break;
			case 'password_reset_mismatch':
				?><div class="error"><p><?php echo 'The passwords do not match.' ?></p></div><?php
				break;
			default:
				break;
		}
	}

}