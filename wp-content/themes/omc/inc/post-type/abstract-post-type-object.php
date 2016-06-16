<?php

namespace OMC;
use \WP_Exception as WP_Exception;
/*
 * Abstract Class of post type
 */ 
abstract class Post_Object {	
	
	// Should declare in child class
	protected $name;
	public $default_meta = array();
	public $custom_settings;
	public $samples = array();
	public $placeholder = array();		
	public $placeholder_cache = array();
	
	/*
	 * Contruct
	 * Load / populate object with id or post object
	 */
	function __construct( $id = 0 ){		

		// Check input
		if( empty( $id ) )
			return false;

		// Get post object
		$post = $this->get_post_object_by_id( $id );		

		return $this->populate( $post );

	}	
	
	/*
	 * Get post object by id
	 */
	function get_post_object_by_id( $id = 0 ){		
		if( is_a( $id, 'WP_Post' ) )
			return $id;
		
		$obj = get_post( $id );	
		
		if( empty( $obj ) )
			throw new WP_Exception( 'Could not retrive the post object', "The object's post_type with id '{$id}' failed to retrive, probably it not exists." );
		
		return $obj;		
	}
	
	/*
	 * Get default post meta
	 */
	function get_default_meta(){
		return apply_filters( 'default_'.$this->name.'_meta', $this->default_meta );
	}

	/*
	 * Populate post object
	 * $obj is WP_Post
	 */
	function populate( $obj = '' ){
		
		if( !is_a( $obj, 'WP_Post' ) )
			throw new WP_Exception( 'Wrong Argument Format', 'The input must WP_Post object.' );
		
		$this->obj = $obj;
		
		// Populate properties
		$this->id = $this->obj->ID;
		$this->post_type = $this->obj->post_type;
		$this->author = $this->obj->post_author;
		$this->slug = $this->obj->post_name;
		$this->title = $this->obj->post_title;
		$this->create_date = $this->obj->post_date;
		$this->description = $this->obj->post_content;
		$this->excerpt = $this->obj->post_excerpt;
		$this->status = $this->obj->post_status;
		$this->modified_date = $this->obj->post_modified;
		$this->comment_count = $this->obj->comment_count;
		$this->menu_order = $this->obj->menu_order;
		$this->can_edit = $this->author == get_current_user_id();
		$this->placeholder = array();

		// Get meta
		$meta = get_post_meta( $this->id, '', true );
		
		// Populate meta
		foreach( $this->get_default_meta() as $key => $value ){
			
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
	 * Check if the object is loaded
	 * Throw error if it is not
	 */
	function check_is_loaded(){
		if( empty( $this->id ) )
			throw new WP_Exception( 'Post Object is not Loaded', 'This post object does not have identity.' );		
	}
	
	/*
	 * Get maximum menu order
	 */
	static function get_max_menu_order(){
		global $wpdb;
		
		$max_menu_order = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(menu_order) FROM $wpdb->posts WHERE post_type = %s", $this->name ) );	
		if( empty( $max_menu_order ) )
			$max_menu_order = 0;
		
		return $max_menu_order;
	}

	/*
	 * Get Meta array
	 * Mostly for the frontend use (json)
	 */
	function get_meta_array(){
		
		// The object must be loaded
		$this->check_is_loaded();

		$arr = (array) $this;		

		// Exclude obj
		unset( $arr['obj'] );

		return $arr;
	}

	/*
	 * Get Post object url
	 */
	static function _get_url( $id = 0 ){
		return apply_filters( $this->name.'_url', get_permalink( $id ), $id );
	}
	function get_url(){
		if( empty( $this->id ) )
			return new WP_Exception( 'Post Object is not Loaded', 'This post object does not have identity.' );
		
		return static::_get_url( $this->id );
	}
	
	/*
	 * Helper function to Register placeholder
	 */
	function register_placeholder( array $data ){		
		$this->placeholder = array_merge( $this->placeholder, $data );
	}
	
	/*
	 * Replace the placeholder to something meaningful
	 */
	function replace_placeholder( $content, $use_cache = true ){
		
		// The object must be loaded
		$this->check_is_loaded();
		
		// Check placeholder property
		if( empty( $this->placeholder ) || !is_array( $this->placeholder ) )
			return $content;
		
		// Check cache
		if( $use_cache && empty( $this->placeholder_cache ) || !$use_cache ){
			
			// Create cache
			foreach( $this->placeholder as $key => $value ){
				// Direct replace
				if( is_string( $key ) )
					$this->placeholder_cache["##{$key}##"] = $value;
				
				elseif( property_exists( $this, $value ) )
					$this->placeholder_cache["##{$value}##"] = $this->$value;					
			}			
		}
		
		// Replace it
		if( !empty( $this->placeholder_cache ) )
			return strtr( $content, $this->placeholder_cache );
		
		return $content;
	}
	
	/*
	 * Create standard template file for the page
	 * It won't override the existing file
	 */
	function create_template_files(){
		
		// The object must be loaded
		$this->check_is_loaded();
		
		// Targeting sample files
		if( !is_array( $this->samples ) )
			throw new WP_Exception( "Wrong format of 'samples' property", "'samples' property expect to be an array" );
		
		// Create
		foreach( $this->samples as $target_dir => $sample ){
			
			$target_dir = $this->replace_placeholder( $target_dir );
			
			foreach( $sample as $sample_name => $target_name ){
				// Sample file check
				$sample_file_path = OMC_SAMPLE_DIR.'/'.$sample_name;
				if( !file_exists( $sample_file_path ) )
					throw new WP_Exception( "Missing Sample File", "Could not find the file: $sample_file_path." );
				
				// Template file check
				$target_name = $this->replace_placeholder( $target_name );
				$target_file_path = $target_dir.'/'.$target_name;
				if( file_exists( $target_file_path ) )
					continue;
				
				if( !is_dir( $target_dir ) ){
					if(!mkdir( $target_dir, 0777, true ))
						throw new WP_Exception( "Fail to create folder", "Could not create folder: $target_dir." );
				}
				// Create file
				if( file_put_contents( $target_file_path, $this->replace_placeholder( file_get_contents( $sample_file_path ) ) ) === false )
					throw new WP_Exception( "Could not create a template file", "Could not create the file: $target_file_path." );
			}			
		}		
	}
	
	/*
	 * Get edit url
	 */
	static function _get_edit_url( $id = 0 ){
		return apply_filters( $this->name.'_edit_url', add_query_arg( 'action', 'edit', static::_get_url( $id ) ), $id );	
	}
	function get_edit_url(){
		// The object must be loaded
		$this->check_is_loaded();

		return static::_get_edit_url( $this->id );
	}
	
	/*
	 * Get Custom Settings
	 */
	function get_custom_settings( $field = '', $default = false ){
		
		// The object must be loaded
		$this->check_is_loaded();
		
		if( empty( $this->custom_settings ) )
			return new WP_Exception( 'Missing Custom Settings object', 'Custom settings object is not defined.' );
		
		return $this->custom_settings->get( $field, $this->id, $default );
	}
	
	/*
	 * Set Custom Settings
	 * Allow partial update
	 */
	function set_custom_settings( array $data, $partial = true ){
		
		// The object must be loaded
		$this->check_is_loaded();
		
		if( empty( $this->custom_settings ) )
			return new WP_Exception( 'Missing Custom Settings object', 'Custom settings object is not defined.' );
		
		if( $partial && ( $settings = $this->get_custom_settings() ) && !empty( $settings ) ){
			$data = array_merge( $this->get_custom_settings(), $data );
		}
		
		return $this->custom_settings->set( $data, $this->id );
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
		$args['post_type'] = $this->name;	

		$args = apply_filters( $this->name.'_query_lists_args', $args );

		return new WP_Query( $args );
	}
	
	/*
	 * Create object
	 */
	static function _create( $meta = array(), $args = array() ){

		// Setup args
		$args = array_merge( $args, array( 'post_status' => 'publish', 'post_type' => $this->name ) );	// Merge with Compulsory field
		unset( $args['ID'] );		// ID must not exists for creation
		
		// Create
		$post_id = wp_insert_post( $args, true );
		if( is_wp_error( $post_id ) )
			throw new WP_Exception( $post_id );

		// Update menu order
		wp_update_post( array( 'ID' => $post_id, 'menu_order' => static::get_max_menu_order() + 1	) );

		// Default meta
		$default_meta = $this->get_default_meta();

		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );
		$meta = $meta + $default_meta;		

		// Loop	
		foreach( $meta as $key => $value )
			update_post_meta( $post_id, $key, $value );		

		return $post_id;
	}
	function create( $meta = array(), $args = array() ){

		$id = static::_create( $meta, $args );
		
		// Get post object and populate
		$post = $this->get_post_object_by_id();		

		return $this->populate( $post );
	}
	
	
	/*
	 * Edit post object's args and meta
	 */
	static function _edit( $meta = array(), $args = array() ){		
		
		// Input check
		if( empty( $args['ID'] ) )
			throw new WP_Exception( 'Missing post id', "{$args['ID']} is not provided." );

		// Setup args
		$args = array_merge( $args, array( 'post_status' => 'publish', 'post_type' => $this->name ) );	// Merge with Compulsory field
		
		// Save edit
		$post_id = wp_update_post( $args, true );
		if( is_wp_error( $post_id ) )
			throw new WP_Exception( $post_id );
		
		// Default meta
		$default_meta = $this->get_default_meta();

		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );

		// Loop	
		foreach( $meta as $key => $value )
			update_post_meta( $post_id, $key, $value );		

		return $post_id;
	}
	/*
	 * Edit post object
	 */
	function edit( $meta = array(), $args = array() ){

		// The object must be loaded
		$this->check_is_loaded();

		// Set id
		$args['ID'] = $this->id;

		// Update
		return static::_update( $meta, $args );
	}

	
	/*
	 * Delete post object
	 */
	static function _delete( $id = 0 ){		
		
		// Input check
		if( empty( $id ) )
			throw new WP_Exception( 'Missing post id', "Post id is not provided." );

		// Hook before delete
		do_action( 'before_delete_'.$this->name );
		

		// Delete post object
		if( wp_delete_post( $id, true ) === false )
			throw new WP_Exception( 'Fail to delete '.$this->name, "Cannot delete the post object with ID = {$id}." );

		return true;
	}	
	function delete(){		
		
		// The object must be loaded
		$this->check_is_loaded();

		return static::_delete( $this->id );
	}

	/*
	 * Sort post object
	 */
	static function _sort( $id = 0, $menu_order = 0 ){	

		// Input check
		if( empty( $id ) )
			throw new WP_Exception( 'Missing post id', "Post id is not provided." );

		global $wpdb;		
		
		// Move item to the top
		if( $menu_order == 0 )
			wp_update_post( array( 'ID' => $id, 'menu_order' => static::get_max_menu_order() + 1	) );
		
		// Make the object with menu order as required and push other down
		else {
			// Update post object before the menu order: +1
			$sql = "
				UPDATE $wpdb->posts SET menu_order = menu_order + 1 
				WHERE post_type = %s 
					AND (post_status = 'publish' OR post_status = 'private') 
					AND menu_order >= %d
			";
			$wpdb->query( $wpdb->prepare( $sql, $this->name, $menu_order ) );
			wp_update_post( array( 'ID' => $id, 'menu_order' => $menu_order	) );
		}
	}
	function sort( $menu_order ){
		
		// The object must be loaded
		$this->check_is_loaded();

		return static::_sort( $this->id );		
	}
}