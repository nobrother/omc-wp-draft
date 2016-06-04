<?php

/*
 * Saved Playlist Class
 */
 
class Saved_Playlist{

	static $playlist_per_page = 2;
	
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
	function populate( $obj ){
		
		// Test incoming object
		if( !empty( $obj ) && is_a( $obj, 'WP_Post' ) && $obj->post_type == 'saved_playlist' )
			$this->obj = $obj;
		
		if( empty( $this->obj ) || $this->obj->post_type !== 'saved_playlist' )
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
		$this->can_edit = $this->author == get_current_user_id();
		$this->playlist = $this->obj->post_parent;
		
		// Default meta
		$default_meta = apply_filters( 'default_saved_playlist_meta', array(
			'playlist_cache' => array(),
			'author_cache' => array(),
		) );
		
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
	 * Get saved playlist meta array
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
	
	function get_playlist_link(){
		if( 
			empty( $this->id ) ||
			empty( $this->playlist )
		)
			return false;
		
		return Playlist::_get_link( $this->playlist );
	}
	
	/*
	 * Create saved playlist
	 */
	function create( $pid = 0, $meta = array(), $args = array() ){
		
		$id = self::_create( $pid, $meta, $args );
		
		// Get post object and populate
		if( $id ){
			$post = get_post( $id );
			$this->populate( $post );
		}
	}
	
	/*
	 * Create playlist
	 */
	static function _create( $pid = 0, $meta = array(), $args = array() ){
		
		if( empty( $pid ) )
			return false;
		
		// Compulsory field for create new video
		$default_args = array(
			'post_status'    => 'publish',
			'post_type'      => 'saved_playlist',
			'post_parent'		 => $pid,
		);
		
		$args = wp_parse_args( $default_args, $args );
		unset( $args['ID'] );		// Forbid to put ID
		
		// Create playlist
		if( !$post_id = wp_insert_post( $args ) )
			return false;
		
		// Update menu order
		wp_update_post(array(
			'ID' => $post_id,
			'menu_order' => $post_id,
		));
		
		// Update playlist meta
		// Default meta
		$default_meta = apply_filters( 'default_saved_playlist_meta', array(
			'playlist_cache' => array(),
			'author_cache' => array(),
		));
		
		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );
		$meta = $meta + $default_meta;
		
		// Loop	
		foreach( $meta as $key => $value ){
			update_post_meta( $post_id, $key, $value );
		}
		
		return $post_id;
	}
	
	/*
	 * Edit saved playlist
	 */
	function edit( $meta = array(), $args = array() ){
		
		if( empty( $this->id ) )
			return false;

		// Set id
		$args['ID'] = $this->id;
		
		// Update
		return self::_update( $meta, $args );		
	}
	
	/*
	 * Update saved playlist
	 */
	static function _update( $meta = array(), $args = array() ){
		
		if( empty( $args['ID'] ) )
			return false;
		
		// Compulsory field for create new video
		$default_args = array(
			'post_status'    => 'publish',
			'post_type'      => 'saved_playlist',
		);
		$args = wp_parse_args( $default_args, $args );
		
		if( !$post_id = wp_update_post( $args ) )
			return false;
		
		// Default meta
		$default_meta = apply_filters( 'default_saved_playlist_meta', array(
			'playlist_cache' => array(),
			'author_cache' => array(),
		));
		
		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );
		
		// Loop	
		foreach( $meta as $key => $value ){
			update_post_meta( $post_id, $key, $value );
		}
		
		return $post_id;
	}
	
	/*
	 * Delete saved playlist
	 */
	function delete(){
		
		if( empty( $this->id ) )
			return false;
		
		return self::_delete( $this );
	}
	
	/*
	 * Delete saved playlist
	 * static
	 */
	static function _delete( $spid = 0 ){
		
		if( empty( $spid ) )
			return false;
		
		if( is_a( $spid, 'Saved_Playlist' ) )
			$saved_playlist = $spid;
		else
			$saved_playlist = new Playlist( $spid );
			
		if( empty( $saved_playlist->id ) )
			return false;
		
		// Delete playlist
		wp_delete_post( $saved_playlist->id );
		
		return true;		
	}
	
	/*
	 * Check is saved?
	 * static
	 */
	static function is_saved( $pid = 0, $uid = 0 ){
		
		if( empty( $pid ) )
			return new WP_Error( 'empty_pid', 'Playlist id is empty' );
		
		if( empty( $uid ) )
			$uid = get_current_user_id();
		
		if( empty( $uid ) )
			return new WP_Error( 'empty_uid', 'User id is empty' );
		
		global $wpdb;
		
		$sql = "
			SELECT EXISTS(
				SELECT 1 FROM $wpdb->posts 
				WHERE post_author = %d
					AND post_parent = %d
					AND post_type = 'saved_playlist'
					AND (post_status = 'publish' OR post_status = 'private')
			)";
		
		return ( $wpdb->get_var( $wpdb->prepare( $sql, $uid, $pid ) ) != 0 );		
	}
}

/*
 * Saved Playlist Ajax Class
 */ 
class Saved_Playlist_Ajax {
	
	protected $action_prefix = 'saved_playlist_';
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		// Load saved playlist
		if( !empty( $_POST['spid'] ) ){
			$this->saved_playlist = new Saved_Playlist( $_POST['spid'] );
			if( empty( $this->saved_playlist ) )
				$this->saved_playlist = false;
		}
		else
			$this->saved_playlist = false;
		
		// Load playlist
		if( !empty( $_POST['pid'] ) ){
			$this->playlist = new Playlist( $_POST['pid'] );
			if( empty( $this->playlist ) )
				$this->playlist = false;
		}
		else
			$this->playlist = false;
		
		// Load user if any
		if( !empty( $_POST['uid'] ) ){
			$this->user = new User( (int) $_POST['uid'] );
			if( empty( $this->user ) )
				$this->user = false;
		}
		else
			$this->user = false;
		
		// Add action hook
		$this->add_ajax( 'create_saved_playlist' );
	}
	
	function create_saved_playlist(){
		
		// Security check
		if( 
			$this->playlist && 
			$this->user &&
			is_user_logged_in() &&
			$this->user->id != get_current_user_id() &&
			!Saved_Playlist::is_saved( $this->playlist->id )
		){

			$args = array(
				'post_title' => $this->playlist->title,
			);
			
			$meta = array(
				'playlist_cache' => $this->playlist->get_meta_array(),
				'author_cache' => $this->user->get_meta_array(),
			);
			
			$saved_playlist = new Saved_Playlist();
			$saved_playlist->create( $this->playlist->id, $meta, $args );
			
			if( empty( $saved_playlist->id ) )
				$output = array( 'error' => 'Fail to save playlist.' );
			else
				$output = $saved_playlist->get_meta_array();
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}

		$output['post'] = $_POST;

		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Add Ajax
	protected function add_ajax( $action, $nopriv = true ){
		
		if( empty( $action ) )
			return false;
		
		if( !method_exists( $this, $action ) )
			return false;
			
		// Add action hook
		add_action( 'wp_ajax_'.$this->action_prefix.$action, array( $this, $action ) );
		if( !empty( $nopriv ) )
			add_action( 'wp_ajax_nopriv_'.$this->action_prefix.$action, array( $this, $action ) );
		
	}
	
	/**
	 * Output headers for JSON requests
	 */
	protected function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}
}

if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
	new Saved_Playlist_Ajax();