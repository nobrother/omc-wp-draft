<?php
namespace OMC\User;
use \WP_Exception as e;

/*
 * User Object Class
 */ 
class Object{
	
	const USERNAME_SALT = '91u~+ qiJN&%!nas nu';
	
	public $default_meta = array();
	
	/*
	 * Contruct
	 * Load / populate object with id or post object
	 */
	function __construct( $id = 0 ){
		
		if( empty( $id ) )
			return false;
		
		// WP_Post object incoming
		if( is_a( $id, 'WP_User' ) )
			return $this->populate( $id );
		
		// Check the input
		if( is_email( $id ) )
			$field = 'email';
		elseif( is_int( $id ) )
			$field = 'id';
		else
			$field = 'login';
		
		$user = get_user_by( $field, $id );		
		return $this->populate( $user );
		
	}
	
	/*
	 * Populate post obj into video
	 * $obj is WP_Post
	 */
	function populate( $obj = '' ){
		
		// Test incoming object
		if( empty( $obj ) || !is_a( $obj, 'WP_User' ) )
			return false;
			
		$this->obj = $obj;			
		
		// Populate properties
		$this->id = $this->obj->ID;
		$this->user_login = $this->obj->user_login;
		$this->user_email = $this->obj->user_email;
		$this->display_name = $this->obj->display_name;
		$this->caps = $this->obj->caps;
		$this->cap_key = $this->obj->cap_key;
		$this->roles = $this->obj->roles;
		$this->allcaps = $this->obj->allcaps;
		$this->first_name = $this->obj->first_name;
		$this->last_name = $this->obj->last_name;		
		
		// Default meta
		$default_meta = $this->get_default_meta();
		
		// Get meta
		$meta = get_user_meta( $this->id );
		
		// Populate meta
		foreach( $default_meta as $key => $value ){
			if( isset( $meta[$key] ) ){
				
				// Follow default meta format: is scalar or not
				if( is_scalar( $value ) )
					$this->$key = array_shift( $meta[$key] );
				else
					$this->$key = maybe_unserialize( $meta[$key] );
			}
			
			// Use default
			else
				$this->$key = $value;
		}
		
		return true;
	}
	
	/*
	 * Get default post meta
	 */
	function get_default_meta(){
		return apply_filters( 'default_user_meta', $this->default_meta );
	}
	
	/*
	 * Get user meta array
	 * Mostly for the frontend use (json)
	 */
	function get_meta_array(){
		
		if( empty( $this->id ) )
			return array();
		
		$arr = (array) $this;
		
		// Exclude obj
		unset( $arr['obj'] );
		
		return $arr;
	}	
}