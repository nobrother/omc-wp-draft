<?php

/*
 * Playlist Class
 */
 
class Playlist{
	
	// Sample videos
	protected static $sample_videos = array(
		117, 		// Almost lover
		34,			// Crazy little things call love
		118,		// Day month year
		12,			// Gladiator
		11,			// The Other Side of Mt. Heart Attack
		9,			// BIGBANG - LOSER
	);
	
	static $video_per_page = 10;
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
		if( !empty( $obj ) && is_a( $obj, 'WP_Post' ) && $obj->post_type == 'playlist' )
			$this->obj = $obj;
		
		if( empty( $this->obj ) || $this->obj->post_type !== 'playlist' )
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
		
		// Default meta
		$default_meta = apply_filters( 'default_playlist_meta', array(
			'playing_mode' => 'full',
			'current_index' => 0,
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
	 * Get playlist meta array
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
	 * Get playlist link
	 */
	static function _get_link( $pid = 0 ){		
		return apply_filters( 'playlist_link', home_url( '/?pid='.$pid ), $pid );		
	}
	function get_link(){
		if( empty( $this->id ) )
			return array();
			
		return self::_get_link( $this->id );
	}
	
	/*
	 * Get edit playlist link
	 */
	static function _get_edit_link( $pid = 0 ){		
		return apply_filters( 'playlist_edit_link', get_permalink( $pid ), $pid );	
	}
	function get_edit_link(){
		if( empty( $this->id ) )
			return array();
			
		return self::_get_edit_link( $this->id );
	}
	
	/*
	 * Get current playlist video
	 */
	function get_videos( $args = array() ){
		if( empty( $this->id ) )
			return false;
			
		return self::_get_videos( $this->id, $args );
	}
	
	/*
	 * Get playlist video
	 * Protected
	 */
	protected static function _get_videos( $playlist_id, $args = array() ){
		$args = wp_parse_args(  $args, array(
			'post_type'   => 'video',
			'post_parent' => $playlist_id,
			'posts_per_page' => self::$video_per_page,
			'orderby' => 'menu_order',
			'order' => 'DESC',
		) );
		
		$args = apply_filters( 'playlist_get_videos_args', $args );
		return new WP_Query( $args );
	}
	
	/*
	 * Get add video nonce key
	 */
	function get_add_video_nonce_key(){
		if( !empty( $this->id ) )
			return 'playlist_add_video'.$this->id.'ohmycode';
		else
			return 'playlist_add_video';
	}
	
	/*
	 * Get add playlist nonce key
	 */
	static function get_add_playlist_nonce_key(){
		return 'playlist_add_playlist'.get_current_user_id().'ohmycode';
	}
	
	/*
	 * Get add playlist edit nonce key
	 */
	function get_edit_playlist_nonce_key(){
		if( !empty( $this->id ) )
			return 'playlist_edit'.$this->id.get_current_user_id().'ohmycode';
		else
			return 'playlist_edit';
	}
	
	/*
	 * Get video page number
	 */
	function get_video_page_number( $vid = 0 ){
		if( empty( $this->id ) || empty( $vid ) )
			return false;
		
		if( is_a( $vid, 'Video' ) )
			$video = $vid;
		else
			$video = new Video( $vid );
		
		if( empty( $video->id ) )
			return false;
		
		if( $video->playlist != $this->id )
			return false;
		
		global $wpdb;
		
		$sql = "
			SELECT COUNT(*) FROM $wpdb->posts a
         WHERE a.post_parent = %d
				 AND a.menu_order >= %d
				 AND a.post_status = 'publish'
				 AND a.post_type = 'video'
		";
		
		$position = $wpdb->get_var( $wpdb->prepare( $sql, $this->id, $video->menu_order ) );
		
		if( empty( $position ) )
			return false;
		
		return ceil( $position / self::$video_per_page );		
	}
	
	/*
	 * Create playlist
	 */
	function create( $meta = array(), $args = array() ){
		$id = self::_create( $meta, $args );
		
		// Get post object and populate
		if( $id ){
			$post = get_post( $id );
			$this->populate( $post );
		}
		
		// Add sample video
		$this->add_sample_video();
		
	}
	
	/*
	 * Edit playlist
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
	 * Delete playlist
	 */
	function delete(){
		
		if( empty( $this->id ) )
			return false;
		
		return self::_delete( $this->id );
	}
	
	/*
	 * Delete playlist
	 * static
	 */
	static function _delete( $pid = 0 ){
		
		if( empty( $pid ) )
			return false;
		
		if( is_a( $pid, 'Playlist' ) )
			$playlist = $pid;
		else
			$playlist = new Playlist( $pid );
			
		if( empty( $playlist->id ) )
			return false;
		
		// Delete videos
		global $post;
		$query = $playlist->get_videos(array(
			'post_per_page' => -1
		));
		
		while( $query->have_posts() ){
			$query->the_post();
			wp_delete_post( $post->ID );
		}
		
		// Delete playlist
		wp_delete_post( $pid );
		
		return true;
		
	}
	
	
	/*
	 * Add sample video to current playlist
	 */
	function add_sample_video( $vid = 0 ){
		
		if( empty( $this->id ) )
			return false;
		
		return self::_add_sample_video( $this, $vid );
	}
	
	
	/*
	 * Update playlist
	 */
	static function _update( $meta = array(), $args = array() ){
		
		if( empty( $args['ID'] ) )
			return false;
		
		// Compulsory field for create new video
		$default_args = array(
			'post_status'    => 'publish',
			'post_type'      => 'playlist',
		);
		$args = wp_parse_args( $default_args, $args );
		
		if( !$post_id = wp_update_post( $args ) )
			return false;
		
		
		// Default meta
		$default_meta = apply_filters( 'default_playlist_meta', array(
			'playing_mode' => 'full',
			'current_index' => 0,
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
	 * Create playlist
	 */
	static function _create( $meta = array(), $args = array() ){
		
		// Compulsory field for create new video
		$default_args = array(
			'post_status'    => 'publish',
			'post_type'      => 'playlist',
		);		
		if( !isset( $args['ID'] ) ){
			$is_create = true;
			$args = wp_parse_args( $default_args, $args );
		} else {
			$is_create = false;
			$args = wp_parse_args( $default_args, $args );
		}
		
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
		$default_meta = apply_filters( 'default_playlist_meta', array(
			'playing_mode' => 'full',
			'current_index' => 0,
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
	
	/*
	 * Choose a sample video
	 */
	static function get_sample_video( $vid = 0 ){
		
		if( empty( $vid ) )
			$vid = self::$sample_videos[array_rand( self::$sample_videos )];
		
		if( empty( $vid ) )
			return false;
		
		$video = new Video( $vid );
		
		if( empty( $video->id ) )
			return false;
		
		return $video;
	}
	
	/*
	 * Add sample video to playlist
	 */
	static function _add_sample_video( $pid = 0, $vid = 0 ){
		
		if( empty( $pid ) )
			return false;
		
		if( is_a( $pid, 'Playlist' ) )
			$playlist = $pid;
		else
			$playlist = new self( $pid );
		
		if( empty( $playlist->id ) )
			return false;		
		
		// Create first video
		$sample_video = self::get_sample_video( $vid );
		
		if( empty( $sample_video ) )
			return false;

		$args = array(
			'post_title' => $sample_video->title,
			'post_content' => $sample_video->description,
			'post_excerpt' => $sample_video->excerpt,
		);

		$meta = array(
			'youtube_vid' => $sample_video->youtube_vid,
			'start_second' => $sample_video->start_second,
			'end_second' => $sample_video->end_second,
			'cover_image' => $sample_video->cover_image,
			'thumbnail_image' => $sample_video->thumbnail_image,
			'playlist_thumbnail_image' => $sample_video->playlist_thumbnail_image,
			'youtube_data' => $sample_video->youtube_data,
		);

		$video = new Video();
		$video->create( $playlist->id, $meta, $args );
		
		if( empty( $video->id ) )
			return false;
		
		return $video;
	}
	
	
	/*
	 * Sort video
	 * Static
	 */
	static function _sort_video( $vid = 0, $pid = 0, $after_menu_order = 0 ){
	
		if( empty( $vid ) || empty( $pid ) )
			return false;
		
		global $wpdb;
		
		// Update video before the menu order: +1
		$sql = "
			UPDATE $wpdb->posts SET menu_order = menu_order + 1 
			WHERE post_parent = %d
				AND post_type = 'video' 
				AND (post_status = 'publish' OR post_status = 'private') 
				AND menu_order > %d
			ORDER BY menu_order DESC
		";
		$wpdb->query( $wpdb->prepare( $sql, $pid, $after_menu_order ) );
		
		// Update menu order
		return array(
			'result' => wp_update_post(array(
				'ID' => $vid,
				'menu_order' => ( $after_menu_order + 1 ),
			)),
			'sql' => $wpdb->prepare( $sql, $pid, $after_menu_order ),
		);
		
	}
	
	/*
	 * Sort video
	 */
	function sort_video( $vid = 0, $next_vid = 0 ){
		
		if( empty( $this->id ) )
			return false;
		
		if( empty( $next_vid ) )
			$after_menu_order = 0;
		
		else{
			
			$video = new Video( $next_vid );
			
			if( empty( $video->id ) )
				return false;
			else
				$after_menu_order = $video->menu_order;
		}
		
		return Playlist::_sort_video( $vid, $this->id, $after_menu_order );
	}
}

/*
 * Playlist Ajax Class
 */ 
class Playlist_Ajax{
	
	protected $action_prefix = 'playlist_';
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		// Load playlist
		if( !empty( $_POST['pid'] ) ){
			$this->playlist = new Playlist( $_POST['pid'] );
			if( empty( $this->playlist ) )
				$this->playlist = false;
		}
		else
			$this->playlist = false;
			
		// Load video
		if( $this->playlist && !empty( $_POST['vid'] ) ){
			$this->video = new Video( $_POST['vid'] );
			if( empty( $this->video ) )
				$this->video = false;
		}
		else
			$this->video = false;
		
		// Add action hook
		$this->add_ajax( 'add_video' );
		$this->add_ajax( 'add_playlist' );
		$this->add_ajax( 'plus_video_count' );
		$this->add_ajax( 'save_video_start_end_time' );
		$this->add_ajax( 'remove_video' );
		$this->add_ajax( 'save_playing_mode' );
		$this->add_ajax( 'save_current_index' );
		$this->add_ajax( 'set_default' );
		$this->add_ajax( 'edit_title' );
		$this->add_ajax( 'delete_playlist' );
		$this->add_ajax( 'load_page' );
		$this->add_ajax( 'sort_video' );
	}
	
	// Load next page
	function sort_video(){
		
		// Security check
		if( 
			$this->playlist && 
			$this->playlist->can_edit &&
			!empty( $_POST['vid'] ) &&
			( $vid = $_POST['vid'] ) &&
			isset( $_POST['next_vid'] )
		){
			
			$next_vid = $_POST['next_vid'];
				
			// Sort
			if( $result = $this->playlist->sort_video( $vid, $next_vid ) )
				$output = array( 'result' => $result );
			else
				$output = array( 'error' => 'Fail to sort' );
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}

		//$output['post'] = $_POST;

		$this->json_headers();
		echo json_encode( $output );

		die();
	}
	
	// Load next page
	function load_page(){
		
		// Security check
		if( 
			$this->playlist &&
			!empty( $_POST['key'] ) &&
			( $key = $_POST['key'] ) &&
			isset( $_POST['listTemplate'] ) &&
			( $template = $_POST['listTemplate'] ) &&
			!empty( $_POST['filters'] ) && 
			( $args = $_POST['filters'] ) &&
			!empty( $_POST['paged'] ) && 
			( $paged = $_POST['paged'] ) &&
			!empty( $args['post_type'] ) && 
			$args['post_type'] == 'video'			
		){
			
			if( $not_in = get_transient( 'pi_not_in_'.$key ) ){
				// Filter not in
				add_filter( 'posts_where', function( $where ) use( $not_in ) {
					global $wpdb;
					$where .= " AND $wpdb->posts.ID NOT IN( $not_in )";
					return $where;
				});
			}
			
			// Set page
			$args['paged'] = $paged;
			
			// Query
			$query = $this->playlist->get_videos( $args );			
			if( $query->have_posts() ){
				
				global $post, $video;
				
				ob_start();
				$last_page = $query->max_num_pages;				
				
				$videos = array();
				
				while( $query->have_posts() ){
					$query->the_post();
					
					$video = new Video( $post );
					$videos[] = $video->get_meta_array();
					
					if( !empty( $template ) ){
						if( is_mobile() )
							get_template_part( $template, 'mobile' );
						else
							get_template_part( $template, 'pc' );
					}
				}
				
				// Get html
				$html = ob_get_clean();
			}
			
			
			// Store not in
			//set_transient( 'pi_not_in_'.$key, ltrim( $not_in, ',' ), DAY_IN_SECONDS );
			
			$output = array(
				'html' => $html,
				'eof' => ( $query->max_num_pages <= $paged ),
				'paged' => $paged,
				'items' => $videos,
			);
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$output['post'] = $_POST;

		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Delete playlist
	function delete_playlist(){
			
		// Security check
		if(
			is_user_logged_in() && 
			$this->playlist && $this->playlist->can_edit
		){
			
			$args = array(
				'post_title' => $_POST['value'],
			);

			$return = $this->playlist->delete();
			
			$output = array( 'return' => $return );
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}

		$this->json_headers();
		echo json_encode( $output );

		die();
	}
	
	// Edit title
	function edit_title(){
		
		// Security check
		if(
			is_user_logged_in() && 
			isset( $_POST['value'] ) &&
			$this->playlist && $this->playlist->can_edit
		){
			
			$args = array(
				'post_title' => $_POST['value'],
			);

			$post_id = $this->playlist->edit( array(), $args );
			
			$output = array( 'post_id' => $post_id );
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}

		$this->json_headers();
		echo json_encode( $output );

		die();
	}
	
	// Set / unset default playlist
	function set_default(){
		
		// Security check
		if(
			is_user_logged_in() && 
			isset( $_POST['value'] ) &&
			$this->playlist && $this->playlist->can_edit
		){
			
			if( $_POST['value'] )
				update_user_meta( get_current_user_id(), 'default_playlist', $this->playlist->id );
				
			$output = array();
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Add new playlist
	function add_playlist(){
		
		// Security check
		if( 
			is_user_logged_in() && 
			!empty( $_POST['playlist_title'] ) &&
			!empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], Playlist::get_add_playlist_nonce_key() ) 
		){
			
			$playlist = new Playlist();
			$playlist->create( array(), array( 'post_title' => $_POST['playlist_title'] ) );
			
			if( !empty( $playlist->id ) ){
				$output = $playlist->get_meta_array();
				$output['redirect'] = home_url( '/?pid='.$playlist->id );
			} else {
				$output = array( 'error' => 'Fail to create playlist.' );
			}
		} else {
			$output = array( 'error' => 'Access denied.', 'post' => $_POST );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Save current index
	function save_current_index(){
		// Security check
		if( is_user_logged_in() && $this->playlist && $this->playlist->can_edit && isset( $_POST['value'] ) ){
			
			update_post_meta( $this->playlist->id, 'current_index', $_POST['value'] );
			
			$output = array();
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Save playing mode
	function save_playing_mode(){
		// Security check
		if( is_user_logged_in() && $this->playlist && $this->playlist->can_edit && !empty( $_POST['value'] ) ){
			
			update_post_meta( $this->playlist->id, 'playing_mode', $_POST['value'] );
			
			$output = array();
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Remove video
	function remove_video(){
		// Security check
		if( is_user_logged_in() && $this->playlist && $this->playlist->can_edit && $this->video && $this->video->playlist == $this->playlist->id ){
			wp_delete_post( $this->video->id, true );
			
			$output = array();
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Save video start end time
	function save_video_start_end_time(){
		
		// Security check
		if( is_user_logged_in() && $this->playlist->can_edit && $this->video && !empty( $_POST['do'] ) ){
			
			// Do
			switch( $_POST['do'] ){
				case 'start':
					if( isset( $_POST['time'] ) )
						update_post_meta( $this->video->id, 'start_second', sprintf( '%.6f', $_POST['time'] ) );
				break;
				
				case 'end':
					if( isset( $_POST['time'] ) )
						update_post_meta( $this->video->id, 'end_second', sprintf( '%.6f', $_POST['time'] ) );
				break;
				
				case 'reset':
					update_post_meta( $this->video->id, 'start_second', 0 );
					update_post_meta( $this->video->id, 'end_second', 0 );
				break;
			}
			
			$output = array();
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
		
	}
	
	// Increment video count
	function plus_video_count(){
	
		// Security check
		if( $this->video ){
			
			if( $this->playlist->can_edit ){
				$this->video->owner_view_count++;
				update_post_meta( $this->video->id, 'owner_view_count', $this->video->owner_view_count );
			} else {
				$this->video->others_view_count++;
				update_post_meta( $this->video->id, 'others_view_count', $this->video->others_view_count );
			}
			
			$output = array();
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Add video
	function add_video(){
		
		// Security check
		if( 
			is_user_logged_in() && 
			$this->playlist && $this->playlist->can_edit && 
			!empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], $this->playlist->get_add_video_nonce_key() ) 
		){
			
			$video = new video();
			$data = $_POST['video_data'];
			$args = array(
				'post_title' => $data['snippet']['title'],
				'post_content' => $data['snippet']['description'],
			);
			
			$meta = array(
				'youtube_vid' => $data['id'],
				'thumbnail_image' => $data['snippet']['thumbnails']['medium']['url'],
				'playlist_thumbnail_image' => $data['snippet']['thumbnails']['default']['url'],
				'youtube_data' => $data,
			);
			
			// Choose cover image
			if( isset( $data['snippet']['thumbnails']['maxres'] ) )
				$meta['cover_image'] = $data['snippet']['thumbnails']['maxres']['url'];

			elseif( isset( $data['snippet']['thumbnails']['high'] ) )
				$meta['cover_image'] = $data['snippet']['thumbnails']['high']['url'];
				
			$video->create( $this->playlist->id, $meta, $args );
			
			if( !empty( $video->id ) ){
				$output = $video->get_meta_array();
			} else {
				$output = array( 'error' => 'Fail to create video.' );
			}
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
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
	new Playlist_Ajax();