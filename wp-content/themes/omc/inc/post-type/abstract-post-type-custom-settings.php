<?php

namespace OMC;

/*
 * Post Object Custom Settings Class
 */
abstract class Post_Object_Custom_Settings{
	
	// Should define in child class
	public $post_type;
	public $key = 'settings';
	public $default = array();
	public $sanitize_type = array();
	
	/**
	 * Helper function to echo html name
	 */
	function html_name( $field = '', $echo = true ){
		
		if( $echo )
			esc_attr_e( $this->key."[$field]" );
		else
			return esc_attr( $this->key."[$field]" );
	}

	/**
	 * Helper function to get page setting
	 */
	function get( $field = '', $post = '', $default = false ){
			
		if( empty( $post ) )
			global $post;
		
		if( is_a( $post, 'WP_Post' ) )
			$post_id = $post->ID;
		else
			$post_id = $post;
		
		if( empty( $post_id ) )
			throw new WP_Exception( 'Missing Post ID', 'Post ID is not provided.' );
		
		$post_meta = get_post_meta( $post_id, $this->key, true );
		
		if( empty( $field ) )
			return ( $post_meta === false && $default !== false ? $default : $post_meta );
		
		if( isset( $post_meta[$field] ) )
			return $post_meta[$field];
		else
			return ( $default !== false ? $default : false );
		
	}

	/**
	 * Helper function to set page settings
	 */
	function set( array $data, $post = '' ){
		
		if( empty( $post ) )
			global $post;
		
		if( is_a( $post, 'WP_Post' ) )
			$post_id = $post->ID;
		else
			$post_id = $post;
		
		if( empty( $post_id ) )
			throw new WP_Exception( 'Missing Post ID', 'Post ID is not provided.' );
		
		// Merge with defaults
		$data = array_merge( $this->default, $data );
		
		return update_post_meta( $post_id, $this->key, $data );
		
	}
}