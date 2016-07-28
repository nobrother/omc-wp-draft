<?php
namespace OMC\User;
use \WP_Exception as e;

/*
 * User Object Class
 */ 
class Object{
	
	const USERNAME_SALT = '91u~+ qiJN&%!nas nu';
	
	public static $default_meta = array(
		'nickname' => '',
		'first_name' => '',
		'last_name' => '',
		'description' => '',
		'avatar' => '',
	);
	
	public static $default_args = array(
		'ID' => '',
		'user_login' => '',
		'user_email' => '',
		'display_name' => '',
		'user_nicename' => '',
		'user_url' => '',
		'user_registered' => '',
		'user_activation_key' => '',
		'user_status' => '',
	);
	
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
		elseif( is_int( $id ) || is_numeric( $id ) )
			$field = 'id';
		else
			$field = 'login';
		
		$user = get_user_by( $field, $id );
		if( false === $user )
			throw new e( 'user_could_not_find', "User with $field '$id' could not be found." );
		
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
		$this->username = $this->obj->user_login;
		$this->email = $this->obj->user_email;
		$this->password_hash = $this->obj->user_pass;
		$this->display_name = $this->obj->display_name;
		$this->nicename = $this->obj->user_nicename;
		$this->url = $this->obj->user_url;
		$this->registered = $this->obj->user_registered;
		$this->activation_key = $this->obj->user_activation_key;
		$this->status = $this->obj->user_status;
		$this->caps = $this->obj->caps;
		$this->cap_key = $this->obj->cap_key;
		$this->roles = $this->obj->roles;
		$this->allcaps = $this->obj->allcaps;

		// Get meta
		$meta = get_user_meta( $this->id );
		
		// Populate meta
		foreach( static::$default_meta as $key => $value ){
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
	
	/*
	 * Action: Change password
	 */
	function change_password( $old_password = '', $new_password = '' ){
			
		// Check password
		if( empty( $old_password ) )
			throw new e( 'change_password_fail|empty_old_password', 'Old password is empty.' );
		if( empty( $new_password ) )
			throw new e( 'change_password_fail|empty_new_password', 'New password is empty.' );		
		
		// User and old password match?
		if( !wp_check_password( $old_password, $this->password_hash, $this->id ) )
			throw new e( 'change_password_fail|old_password_not_match', "The old password is not matched with the current one." );
		
		// Reset password
		wp_set_password( $new_password, $this->id );
		
	}
	
	/*
	 * Edit
	 */
	function edit( $args = array(), $meta = array() ){
		
		$id = $this->id;
		
		// Check input
		if( !empty( $args ) ){
			
			$args = array_intersect_key( $args, static::$default_args );
			
			$args['ID'] = $this->id;
			unset( $args['user_login'], $args['user_email'] );		// Cannot edit username and email
		}
		if( !empty( $meta ) )
			$meta = array_intersect_key( $meta, static::$default_meta );
		
		// Update userdata
		if( !empty( $args ) ){
			$user_id = wp_update_user( $args );
			if( is_wp_error( $user_id ) )
				throw new e( $user_id );
		}
		
		// Update user meta
		if( !empty( $meta ) ){
			foreach( $meta as $key => $value )
				update_user_meta( $id, $key, $value );
		}		
	}
	
	/*
	 * Get avatar
	 */
	function get_avatar( $width = 50, $height = 50, $crop = true ){

		if( 
			empty( $this->avatar ) ||
			false === ( $url = omc_image_url( $this->avatar, $width, $height, $crop ) ) 
		)	return Main::$defaults['avatar'];
		
		return omc_image_url( $this->avatar, $width, $height, $crop );
	}
}