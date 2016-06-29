<?php
namespace OMC\User;
use OMC\Post_Object_Ajax as ajx;
use \WP_Exception as e;

/*
 * User Ajax Class
 */ 
class Ajax extends ajx{
	
	protected $action_prefix = 'omc_user_';
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		// Load user if any
		if( !empty( $_POST['uid'] ) ){
			$this->obj = new Object( $_POST['uid'] );
			if( empty( $this->obj ) )
				$this->obj = false;
		}
		else
			$this->obj = false;
		
		// Add action hook
		$this->add_ajax( 'login' );		
		$this->add_ajax( 'register' );		
		$this->add_ajax( 'check_new_username' );		
		$this->add_ajax( 'check_new_email' );		
	}
	
	/*
	 * Login
	 */
	function login(){
		
		try{
			
			// Security check
			if( omc_verify_nonce( 'user_login' ) )
				throw new e( 'login_fail', 'Security check fail.' );
			
			// Login
			Main::login( $_POST );
			
			// Success
			$this->return_result( array(
				'status' => '1',
				'redirect_to' => home_url()
			) );			
		} 
		
		// Error handling
		catch( e $e ){			
			$this->return_result( $this->error_result( $e ) );			
		} catch( Exception $e ){			
			$this->return_result( $this->error_result( $e ) );
		}
	}
	
	/*
	 * Register
	 */
	function register(){
		
		try{
			
			// Security check
			if( omc_verify_nonce( 'user_register' ) )
				throw new e( 'register_fail', 'Security check fail.' );
			
			// Register
			$user_id = Main::register( $_POST );
			
			// Success
			$this->return_result( array(
				'status' => '1',
				'redirect_to' => Main::get( 'need_activation' ) ? false : home_url()
			) );			
		} 
		
		// Error handling
		catch( e $e ){			
			$this->return_result( $this->error_result( $e ), true );			
		} catch( Exception $e ){			
			$this->return_result( $this->error_result( $e ) );
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
			$this->return_result( array(
				'status' => '1'
			) );			
		} 
		
		// Error handling
		catch( e $e ){
			$this->return_result( $this->error_result( $e ) );			
		} catch( Exception $e ){			
			$this->return_result( $this->error_result( $e ) );
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
			$this->return_result( array(
				'status' => '1'
			) );			
		} 
		
		// Error handling
		catch( e $e ){
			$this->return_result( $this->error_result( $e ) );			
		} catch( Exception $e ){			
			$this->return_result( $this->error_result( $e ) );
		}
	}
}

new Ajax();