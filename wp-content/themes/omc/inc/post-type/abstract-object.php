<?php

namespace OMC\Post_Type;
use \WP_Exception as e;

/*
 * Abstract Class of post type
 */ 
abstract class Abstract_Object {	
	
	// Should declare in child class
	public static $default_meta = array();
	public $placeholder = array();
	protected $cache = array();
	
	const BEFORE = '##';
	const AFTER = '##';
	
	
	/*
	 * Format of the sample array is
	 * array(
			'<Target folder to save the created file>' => array(
				'<Sample file name in template folder>' => '<Filename of the created file>',
				...
			)
		)
	 * For example
	 * array(
			OMC_TEMPLATE_DIR.'/page/'.'##slug##' => array(
				'page-layout.php' => 'layout.php', 
				'page-content.php' => 'content.php', 
				'page-style.less' => 'style.less',
			)
		)
	 */
	public $samples = array();
	
	/*
	 * Contruct
	 * Load / populate object with id or post object
	 */
	function __construct( $id = 0 ){

		// Check input
		if( empty( $id ) )
			return false;
		
		// WP_Post object incoming
		if( is_a( $id, 'WP_Post' ) )
			return $this->populate( $id );
		
		// Get post object
		$post = get_post( $id );
		if( is_null( $post ) || !is_a( $post, 'WP_Post' ) )
			throw new e( 'post_could_not_find', "Post with ID '$id' could not be found." );

		return $this->populate( $post );
	}
	
	/*
	 * Populate post object
	 * $obj is WP_Post
	 */
	protected function populate( $obj = '' ){
		
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

		// Get meta
		$meta = get_post_meta( $this->id, '', true );
		
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
	 * Get Post object url
	 */
	function get_url(){
		if( false === ( $url = $this->get_cache( 'url' ) ) ){
			$url = get_permalink( $this->id );
			$this->save_cache( 'url', $url );
		}
			
		return $url;
	}
	
	/*
	 * Replace the placeholder to something meaningful
	 */
	function replace_placeholder( $content, $use_cache = true ){
		
		// Check placeholder property
		if( empty( $this->placeholder ) || !is_array( $this->placeholder ) )
			return $content;
		
		// Check cache
		if( 
			$use_cache && false === ( $placeholder = $this->get_cache( 'placeholder' ) ) 
			|| !$use_cache 
		){
			$placeholder = array();
			
			foreach( $this->placeholder as $key => $value ){
				// Direct replace
				if( is_string( $key ) )
					$placeholder[self::BEFORE.$key.self::AFTER] = $value;
				
				// Follow object properties
				elseif( property_exists( $this, $value ) )
					$placeholder[self::BEFORE.$value.self::AFTER] = $this->$value;					
			}
			
			$this->save_cache( 'placeholder', $placeholder );
		}
		
		// Replace it
		if( !empty( $placeholder ) )
			return strtr( $content, $placeholder );
		
		return $content;
	}
	
	/*
	 * Create standard template file for the page
	 * It won't override the existing file
	 */
	function create_template_files(){
		
		// Targeting sample files
		if( !is_array( $this->samples ) )
			throw new WP_Exception( "Wrong format of 'samples' property", "'samples' property expect to be an array" );
		
		// Create
		foreach( $this->samples as $target_dir => $sample ){
			
			$target_dir = $this->replace_placeholder( $target_dir );
			
			foreach( $sample as $sample_name => $target_name ){
				// Sample file check
				$sample_name = $this->replace_placeholder( $sample_name );
				$sample_file_path = OMC_SAMPLE_DIR.'/'.$sample_name;
				if( !file_exists( $sample_file_path ) )
					throw new WP_Exception( "{$this->post_type}|missing_sample_file", "Could not find the file: $sample_file_path." );
				
				// Template file check
				$target_name = $this->replace_placeholder( $target_name );
				$target_file_path = $target_dir.'/'.$target_name;
				if( file_exists( $target_file_path ) )
					continue;
				
				if( !is_dir( $target_dir ) ){
					if(!mkdir( $target_dir, 0777, true ))
						throw new WP_Exception( "{$this->post_type}|create_folder_fail", "Could not create folder: $target_dir." );
				}
				// Create file
				if( file_put_contents( $target_file_path, $this->replace_placeholder( file_get_contents( $sample_file_path ) ) ) === false )
					throw new WP_Exception( "{$this->post_type}|create_template_file_fail", "Could not create the file: $target_file_path." );
			}	
		}		
	}
	
	/*
	 * Get edit url
	 */
	function get_edit_url(){
		return get_edit_post_link( $this->id );
	}
	
	/*
	 * Edit post object's args and meta
	 */
	function edit( $meta = array(), $args = array() ){
		
		$args['ID'] = $this->id;

		// Setup args
		$args = array_merge( $args, array( 'post_status' => 'publish', 'post_type' => $this->post_type ) );	// Merge with Compulsory field
		
		// Save edit
		$post_id = wp_update_post( $args, true );
		if( is_wp_error( $post_id ) )
			throw new WP_Exception( $post_id );
		
		// Default meta
		$default_meta = static::$default_meta;

		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );

		// Loop	
		foreach( $meta as $key => $value )
			update_post_meta( $post_id, $key, $value );		

		return $post_id;
	}
	
	/*
	 * Delete post object
	 */
	function delete(){			

		// Delete post object
		if( wp_delete_post( $this->id, true ) === false )
			throw new WP_Exception( 'Fail to delete '.$this->post_type, "Cannot delete the post object with ID = {$this->id}." );

		return true;
	}
}