<?php

namespace OMC\Taxonomy;
use \WP_Exception as e;

/*
 * Abstract Class of post type
 */ 
abstract class Abstract_Object {	
	
	// Should declare in child class
	public static $taxonomy;
	public static $default_meta;
	
	/*
	 * Contruct
	 * Load / populate object with id or post object
	 */
	function __construct( $id = 0, $field = 'slug' ){

		if( empty( $id ) )
			return false;
		
		// WP_Post object incoming
		if( is_a( $id, 'WP_Term' ) ){
			if( $id->taxonomy !== static::$taxonomy )
				throw new e( 'load_object_fail|term_taxonomy_not_match', 'We do not expect object with taxonomy <'.$id->taxonomy.'>' );
			return $this->populate( $id );
		}
		
		// Check the input
		if( is_int( $id ) || is_numeric( $id ) ){
			$field = 'id';
			$id = (int) $id;
		}
		
		$term = get_term_by( $field, $id, static::$taxonomy );
		if( false === $term )
			throw new e( 'load_object_fail|could_not_find_term', "Term with $field '$id' could not be found." );
		
		return $this->populate( $term );
	}
	
	/*
	 * Populate term object
	 * $obj is WP_Term
	 */
	protected function populate( $obj = '' ){
		
		$this->obj = $obj;
		
		// Populate properties
		$this->id = $this->obj->term_id;
		$this->name = $this->obj->name;
		$this->slug = $this->obj->slug;
		$this->term_group = $this->obj->term_group;
		$this->term_taxonomy_id = $this->obj->term_taxonomy_id;
		$this->taxonomy = $this->obj->taxonomy;
		$this->description = $this->obj->description;
		$this->parent = $this->obj->parent;
		$this->count = $this->obj->count;

		// Get meta
		$meta = get_term_meta( $this->id, '', true );
		
		// Populate meta
		foreach( static::$default_meta as $key => $value ){
			
			if( isset( $meta[$key] ) ){

				// Follow default meta format: is scalar or not
				if( is_scalar( $value ) )
					$this->$key = $meta[$key][0];
				else
					$this->$key = maybe_unserialize( $meta[$key][0] );
			}			

			// Use default
			else
				$this->$key = $value;
		}

		return true;
	}
	
	/*
	 * Get cache
	 */
	protected function get_cache( $key ){
		if( isset( $this->cache[$key] ) )
			return $this->cache[$key];
		return false;
	}
	
	/*
	 * Save cache
	 */
	protected function save_cache( $key, $value ){
		$this->cache[$key] = $value;
	}

	/*
	 * Get Meta array
	 * Mostly for the frontend use (json)
	 */
	function get_meta_array(){

		$arr = (array) $this;		

		// Exclude obj
		unset( $arr['obj'], $arr['cache'] );

		return $arr;
	}

	/*
	 * Get term archive url
	 */
	function get_url(){
		if( false === ( $url = $this->get_cache( 'url' ) ) ){
			$url = get_term_link( $this->obj );
			$this->save_cache( 'url', $url );
		}
			
		return $url;
	}
	
	/*
	 * Edit post object's args and meta
	 */
	function edit( $meta = array(), $args = array() ){
		
		// Update term
		if( !empty( $args ) ){
			$return = wp_update_term( $this->id, $this->taxonomy, $args );
			if( is_wp_error( $return ) )
				throw new e( $return );
		}
		
		// Update meta
		$default_meta = static::$default_meta;
		$meta = array_intersect_key( $meta, $default_meta );
		
		// Loop	
		foreach( $meta as $key => $value )
			update_term_meta( $this->id, $key, $value );

		return $this;
	}
	
	/*
	 * Delete term
	 */
	function delete(){			

		// Delete term
		$return = wp_delete_term( $this->id, $this->taxonomy );
		
		if( is_wp_error( $return ) )
			throw new e( $return );
		
		return true;
	}
}