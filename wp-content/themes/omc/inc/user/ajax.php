<?php
namespace OMC\User;
use OMC\Abstract_Ajax as ajx;
use \WP_Exception as e;

/*
 * User Ajax Class
 */ 
class Ajax extends ajx{
	
	protected static $action_prefix = 'omc_user_';
	protected static $object_classname = '\OMC\User\Object';
	
	/*
	 * Contruct
	 */
	static function init(){
		
		// Add action hook
		static::add_ajax( 'login' );		
		static::add_ajax( 'register' );		
		static::add_ajax( 'change_password' );		
		static::add_ajax( 'check_new_username' );		
		static::add_ajax( 'check_new_email' );
		static::add_ajax( 'edit_account' );
		static::add_ajax( 'reload_capabilities' );
	}
	
	/*
	 * Reload capabilities
	 */
	static function reload_capabilities(){
		try{
			// Check user right
			if( !current_user_can( 'manage_options' ) )
				throw new e( 'reload_capabilities_fail|hacker_alert', 'Your have not enough rank to do this.' );
			
			// Check user info
			$obj = static::get_object( 'uid' );
			
			// Security check
			if( !omc_verify_nonce( 'omc_user_reload_capabilities', $obj->email ) )
				throw new e( 'reload_capabilities_fail|hacker_alert', 'Security check fail.' );
			
			// Do action
			do_action( 'omc_user_reload_capbilities' );
			
			// Success
			static::return_result( array(	'status' => '1'	) );
			
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
	
	/*
	 * Edit account / profile
	 */
	static function edit_account(){
		
		try{
			// Check user info
			$obj = static::get_object( 'uid' );			
			
			// Security check
			if( !omc_verify_nonce( 'omc_user_edit_account', $obj->email ) )
				throw new e( 'edit_account_fail|hacker_alert', 'Security check fail.' );
			
			// Save
			$obj->edit( $_POST, $_POST );
			
			// Success
			static::return_result( array(
				'status' => '1',
				'redirect_to' => home_url()
			), true );
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
	
	/*
	 * Login
	 */
	static function login(){
		
		try{
			
			// Security check
			if( !omc_verify_nonce( 'omc_user_login' ) )
				throw new e( 'login_fail', 'Security check fail.' );
			
			// Login
			Main::login( $_POST );
			
			// Success
			static::return_result( array(
				'status' => '1',
				'redirect_to' => home_url()
			) );
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
	
	/*
	 * Register
	 */
	static function register(){
		
		try{
			// Security check
			if( !omc_verify_nonce( 'omc_user_register' ) )
				throw new e( 'register_fail', 'Security check fail.' );
			
			// Register
			$user_id = Main::register( $_POST );
			
			// Success
			static::return_result( array(
				'status' => '1',
				'redirect_to' => Main::get( 'need_activation' ) ? false : home_url()
			) );
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
	
	/*
	 * Check new username
	 */
	function check_new_username(){
		
		try{
			// Security check
			if( empty( $_POST['value'] ) )
				throw new e( 'check_new_username_fail', 'Does not provide username' );
			
			if( username_exists( $_POST['value'] ) )
				throw new e( 'check_new_username_fail', 'Username exists.' );
				
			// Success
			static::return_result( array(
				'status' => '1'
			) );
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
	
	/*
	 * Check new email
	 */
	function check_new_email(){
		
		try{
			// Variable check
			if( empty( $_POST['value'] ) )
				throw new e( 'check_new_email_fail', 'Does not provide email' );
			
			$email = $_POST['value'];
			if( !is_email( $email ) )
				throw new e( 'check_new_email_fail', '<'.$email.'> is not an email.' );
			
			if( email_exists( $email ) )
				throw new e( 'check_new_username_fail', '<'.$email.'> has been used.' );
				
			// Success
			static::return_result( array(
				'status' => '1'
			) );
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
	
	/*
	 * Check new email
	 */
	function change_password(){
		
		try{
			// Check user info
			$obj = static::get_object( 'uid' );
			
			// Security check
			if( !omc_verify_nonce( 'omc_user_change_password', $obj->email ) )
				throw new e( 'change_password_fail|hacker_alert', 'Security check fail.' );
			
			// Variable check
			if( !isset( $_POST['old_password'], $_POST['user_pass'] ) )
				throw new e( 'change_password_fail|missing_password', 'Passwords are not provided.' );
			
			// Load current user
			$current_user = wp_get_current_user();
			if ( 0 === $current_user->ID )
				throw new e( 'change_password_fail|user_does_not_login', 'Not user is logging in.' );
			
			//Change password
			$user = new Object( $current_user );
			$user->change_password( $_POST['old_password'], $_POST['user_pass'] );
			
			// Success
			static::return_result( array(
				'status' => '1',
			) );
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}	
}

// Initialize
try{	
	Ajax::init();
} catch( e $e ) {
	Ajax::return_result( Ajax::error_result( $e ), true );			
} catch( \Exception $e ){
	Ajax::return_result( Ajax::error_result( $e ) );
}