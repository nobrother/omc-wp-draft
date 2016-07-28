<?php

namespace OMC\Post_Type;
use \WP_Exception as WP_Exception;
/*
 * Abstract Class of post type
 */ 
abstract class Abstract_Main {	
	
	protected static $post_type = '';
	protected static $object_class = '';
	
	static function init(){	}
	
	/*
	 * Get maximum menu order
	 */
	static function get_max_menu_order(){
		global $wpdb;
		
		$max_menu_order = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(menu_order) FROM $wpdb->posts WHERE post_type = %s", static::$post_type ) );	
		if( empty( $max_menu_order ) )
			$max_menu_order = 0;
		
		return $max_menu_order;
	}
	
	/*
	 * Create object
	 */
	static function create( $meta = array(), $args = array() ){

		// Setup args
		$args = array_merge( $args, array( 'post_status' => 'publish', 'post_type' => static::$post_type  ) );	// Merge with Compulsory field
		unset( $args['ID'] );		// ID must not exists for creation
		
		// Create
		$post_id = wp_insert_post( $args, true );
		if( is_wp_error( $post_id ) )
			throw new WP_Exception( $post_id );
		
		// Update menu order
		wp_update_post( array( 'ID' => $post_id, 'menu_order' => static::get_max_menu_order() + 1	) );
		
		// Default meta
		$default_meta = $object_class::$default_meta;

		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );
		$meta = $meta + $default_meta;		

		// Loop	
		foreach( $meta as $key => $value )
			update_post_meta( $post_id, $key, $value );		

		return new static::$object_class;
	}
	
	/*
	 * Query post object list
	 */
	static function query_lists( $args = array() ){
		
		// Default args
		$args = array_merge( array(
			'orderby' => 'menu_order',
			'order' => 'DESC',
		), $args );
		
		// Mondatory field
		$args['post_type'] = static::$post_type;	
		
		return new WP_Query( $args );
	}
}