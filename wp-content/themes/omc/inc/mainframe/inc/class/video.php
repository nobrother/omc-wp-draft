<?php

/*
 * Video Class
 */
 
class Video{

	/*
	 * Contruct
	 * Load / populate object with id or post object
	 */
	function __construct( $id = 0 ){
		
		if( empty( $id ) )
			return false;
		
		// WP_Post object incoming
		if( is_a( $id, 'WP_Post' ) )
			return $this->populate( $id );
		
		// Get post object
		$post = get_post( $id );		
		return $this->populate( $post );
		
	}
	
	/*
	 * Populate post obj into video
	 * $obj is WP_Post
	 */
	function populate( $obj = '' ){
		
		// Test incoming object
		if( !empty( $obj ) && is_a( $obj, 'WP_Post' ) && $obj->post_type == 'video' )
			$this->obj = $obj;
		
		if( empty( $this->obj ) || $this->obj->post_type !== 'video' )
			return false;
		
		// Populate properties
		$this->id = $this->obj->ID;
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
		$this->playlist = $this->obj->post_parent;
		
		// Default meta
		$default_meta = apply_filters( 'default_video_meta', array(
			'type' => 'youtube',
			'youtube_vid' => '',
			'start_second' => 0,
			'end_second' => 0,
			'cover_image' => '',
			'thumbnail_image' => '',
			'playlist_thumbnail_image' => omc_image_url( 14, 120, 90 ),
			'owner_view_count' => 0,
			'others_view_count' => 0,
			'like_count' => 0,
			'youtube_data' => array(),
		));
		
		// Get meta
		$meta = get_post_meta( $this->id );
		
		// Populate meta
		foreach( $default_meta as $key => $value ){
			if( isset( $meta[$key] ) ){
				
				// Follow default meta format: is scalar or not
				if( is_scalar( $value ) )
					$this->$key = array_shift( $meta[$key] );
				else
					$this->$key = maybe_unserialize( array_shift( $meta[$key] ) );
			}
			
			// Use default
			else
				$this->$key = $value;
		}
		
		return true;
	}
	
	/*
	 * Get video meta array
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
	 * Get video link
	 */
	function get_link(){
		if( empty( $this->id ) )
			return array();
			
		return home_url( '/?pid='.$this->playlist.'&vid='.$this->id );
	}
	
	
	/*
	 * Create video
	 */
	function create( $playlist_id = 0, $meta = array(), $args = array() ){
		$id = self::_create( $playlist_id, $meta, $args );
		
		// Get post object and populate
		if( $id ){
			$post = get_post( $id );
			$this->populate( $post );
		}
		
	}
	
	
	/*
	 * Create or update video
	 */
	static function _create( $playlist_id = 0, $meta = array(), $args = array() ){
		
		if( empty( $playlist_id ) )
			return false;
		
		// Compulsory field for create new video
		$default_args = array(
			'post_status'    => 'publish',
			'post_type'      => 'video',
			'post_parent'		 => $playlist_id,
		);		
		if( !isset( $args['ID'] ) ){
			$is_create = true;
			$args = wp_parse_args( $default_args, $args );
		} else {
			$is_create = false;
		}
		
		// Create video
		if( !$post_id = wp_insert_post( $args ) )
			return false;
		
		// Update menu order
		wp_update_post(array(
			'ID' => $post_id,
			'menu_order' => $post_id,
		));
			
		// Update video meta
		// Default meta
		$default_meta = apply_filters( 'default_video_meta', array(
			'type' => 'youtube',
			'youtube_vid' => '',
			'start_second' => 0,
			'end_second' => 0,
			'cover_image' => '',
			'thumbnail_image' => '',
			'playlist_thumbnail_image' => omc_image_url( 14, 120, 90 ),
			'owner_view_count' => 0,
			'others_view_count' => 0,
			'like_count' => 0,
			'youtube_data' => array(),
		));
		
		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );		
		if( $is_create )
			$meta = $meta + $default_meta;
		
		// Loop	
		foreach( $meta as $key => $value ){
			update_post_meta( $post_id, $key, $value );
		}
		
		return $post_id;
	}
	
}