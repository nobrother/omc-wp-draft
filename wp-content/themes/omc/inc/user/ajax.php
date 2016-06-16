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
	}
	
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
}

new Ajax();