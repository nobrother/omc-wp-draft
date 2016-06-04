<?php

/*
 * Moment Class
 */
 
class Moment{
	
	/*
	 * Default meta
	 */
	public static $default_meta = array(
		'photos' => array(),
		'like_count' => 0,
		'like_user_hash' => '',
		'unique_user_id' => '',
		'user_ip' => '',
		'source' => '',
		'view_count' => 0,
		'capture_date' => '',
	);
	
	/*
	 * Default photo placeholder
	 */
	public static $default_photo = 'http://www.fillmurray.com/800/800';
	
	/*
	 * Default single photo size
	 */
	public static $default_single_photo_size = array(
		'phone' => 375,
		'tablet' => 900,
		'pc' => 1170,
	);
	
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
		if( !empty( $obj ) && is_a( $obj, 'WP_Post' ) && $obj->post_type == 'moment' )
			$this->obj = $obj;
		
		if( empty( $this->obj ) || $this->obj->post_type !== 'moment' )
			return false;
		
		// Populate properties
		$this->id = $this->obj->ID;
		$this->author = $this->obj->post_author;
		$this->slug = $this->obj->post_name;
		$this->title = $this->obj->post_title;
		$this->create_date = $this->obj->post_date;
		$this->caption = $this->obj->post_content;
		$this->excerpt = $this->obj->post_excerpt;
		$this->status = $this->obj->post_status;
		$this->modified_date = $this->obj->post_modified;
		$this->comment_count = $this->obj->comment_count;
		$this->can_edit = $this->author == get_current_user_id();
		
		// Default meta
		$default_meta = self::get_default_meta();
		
		// Get meta
		$meta = get_post_meta( $this->id );
		
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
	 * Check Is liked
	 */
	function is_liked(){
		if( empty( $this->id ) )
			return new WP_Error( 'moment_not_loaded', 'Moment is empty' );
			
		if( empty( $_COOKIE['unique_user_id'] ) )
			return new WP_Error( 'unique_user_id_not_set', 'Unknown user' );
		
		return strpos( $this->like_user_hash, '|'.$_COOKIE['unique_user_id'].'|' ) !== false;
		
	}
	
	/*
	 * Plus view count
	 * Use this before header is sent
	 */
	function plus_view_count(){
		
		if( empty( $this->id ) )
			return false;
		
		// Start session if not yet start
		maybe_start_session();
		
		// Had view in this session		
		if( !empty( $_SESSION['moment_'.$this->id]['viewed'] ) )
			return false;
		
		// Set session
		$_SESSION['moment_'.$this->id]['viewed'] = 1;
		
		global $wpdb;
		
		// Start transaction
		$wpdb->query( 'START TRANSACTION' );
		
		$this->view_count = get_post_meta( $this->id, 'view_count', true );
		update_post_meta( $this->id, 'view_count', ++$this->view_count );
		
		// Commit transaction
		$wpdb->query( 'COMMIT' );
		
		
		return $this->view_count;
	}
	
	/*
	 * Toggle like
	 */
	function toggle_like(){
		if( is_wp_error( $liked = $this->is_liked() ) )
			return $liked;
		
		$needle = '|'.$_COOKIE['unique_user_id'].'|';
		
		global $wpdb;
		
		// Start transaction
		$wpdb->query( 'START TRANSACTION' );
		
		// Reload Meta
		$this->like_user_hash = get_post_meta( $this->id, 'like_user_hash', true );
		$this->like_count = get_post_meta( $this->id, 'like_count', true );
		
		// Unlike it
		if( $liked ){
			$this->like_user_hash = str_replace( $needle, '', $this->like_user_hash );
			$this->like_count = max( --$this->like_count, 0 );
		}
		
		// Like it
		else {
			$this->like_user_hash .= $needle;
			++$this->like_count;
		}
		
		// Store
		update_post_meta( $this->id, 'like_user_hash', $this->like_user_hash );
		update_post_meta( $this->id, 'like_count', $this->like_count );
		
		// Commit transaction
		$wpdb->query( 'COMMIT' );
		
		return !$liked;
	}
	
	
	/*
	 * Get meta array
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
	
	/**
	 * Get default meta
	 */	
	static function get_default_meta(){
		return apply_filters( 'default_omc_user_meta', self::$default_meta );
	}
	
	/*
	 * Get create moment nonce key
	 */
	static function get_create_moment_nonce_key(){
		return 'create_moment_post'.$_COOKIE['unique_user_id'].'ohmycode';
	}
	
	/*
	 * Get upload photo nonce key
	 */
	static function get_upload_photo_nonce_key(){
		return 'upload_moment_photo'.$_COOKIE['unique_user_id'].'ohmycode';
	}
	
	/*
	 * Create moment
	 */
	function create( $meta = array(), $args = array() ){
		if( is_wp_error( $id = self::_create_or_update( $meta, $args ) ) )
			return $id;
		
		// Get post object and populate
		$post = get_post( $id );
		$this->populate( $post );
		
	}
	
	
	/*
	 * Create or update moment
	 */
	static function _create_or_update( $meta = array(), $args = array() ){
		
		// Compulsory field for create new video
		$default_args = array(
			'post_status'    => 'publish',
			'post_type'      => 'moment',
		);		
		if( !isset( $args['ID'] ) ){
			$is_create = true;
			$args = wp_parse_args( $default_args, $args );
		} else {
			$is_create = false;
		}
		
		// Create
		if( !$post_id = wp_insert_post( $args, true ) )
			return $post_id;
		
		// Update meta
		// Default meta
		$default_meta = self::get_default_meta();
		
		// Sanitize $meta
		$meta = array_intersect_key( $meta, $default_meta );		
		if( $is_create )
			$meta = $meta + $default_meta;
		
		// Loop	
		foreach( $meta as $key => $value ){
			update_post_meta( $post_id, $key, $value );
		}
		
		// Set thumbnail photo
		if( !empty( $meta['photos'] ) && is_array( $meta['photos'] ) ){
			if( $photo = current( $meta['photos'] ) )
				set_post_thumbnail( $post_id, $photo );
		}
		
		return $post_id;
	}
	
}

/*
 * Ajax Class
 */ 
class Moment_Ajax{
	
	protected $action_prefix = 'moment_';
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		// Load moment if any
		if( !empty( $_POST['mid'] ) ){
			$this->moment = new Moment( $_POST['mid'] );
			if( empty( $this->moment ) )
				$this->moment = false;
		}
		else
			$this->moment = false;
		
		// Add action hook
		$this->add_ajax( 'get_next_page' );
		$this->add_ajax( 'toggle_like' );
		$this->add_ajax( 'upload_photo' );
		$this->add_ajax( 'create_moment' );
		$this->add_ajax( 'search_moment_tag' );
	}
	
	// Search moment tag
	function search_moment_tag(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['q'] )
		){
			
			// Sanitize search query
			$s = wp_unslash( $_POST['q'] );

			$comma = _x( ',', 'tag delimiter' );
			if ( ',' !== $comma )
				$s = str_replace( $comma, ',', $s );
			if ( false !== strpos( $s, ',' ) ) {
				$s = explode( ',', $s );
				$s = $s[count( $s ) - 1];
			}
			$s = trim( $s );
			
			// Search terms
			$args = array( 
				'name__like' => $s, 
				'fields' => 'names', 
				'hide_empty' => false, 
			);
			if( is_wp_error( $output = get_terms( 'moment_tag', $args ) ) )
				$output = array();
		
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Create moment
	function create_moment(){
	
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['_wpnonce'] ) &&
			!empty( $_POST['photos'] ) &&
			!empty( $_POST['moment_event'] ) &&
			!empty( $_POST['moment_location'] ) &&
			!empty( $_POST['moment_type'] ) &&
			!empty( $_POST['moment_tag'] ) &&
			!empty( $_POST['caption'] ) &&
			wp_verify_nonce( $_POST['_wpnonce'], Moment::get_create_moment_nonce_key() )
		){
			
			$args = array(
				'post_title' => strip_tags( $_POST['caption'] ),
				'post_content' => $_POST['caption'],				
			);
			
			$meta = array(
				'unique_user_id' => $_COOKIE['unique_user_id'],
				'user_ip' => get_user_ip(),
				'photos' =>$_POST['photos'],
				'source' => !empty( $_POST['source'] ) && is_url( $_POST['source'] ) ? $_POST['source'] : '',
				'capture_date' => !empty( $_POST['capture_date'] ) && strtotime( $_POST['capture_date'] ) ? $_POST['capture_date'] : '',
			);
			
			// Create
			$moment = new Moment();
			if( !is_wp_error( $post_id = $moment->create( $meta, $args ) ) ){
				
				// Update the taxonomy
				wp_set_object_terms( $moment->id, (int) $_POST['moment_event'], 'moment_event' );
				wp_set_object_terms( $moment->id, (int) $_POST['moment_location'], 'moment_location' );
				wp_set_object_terms( $moment->id, (int) $_POST['moment_type'], 'moment_type' );
				wp_set_object_terms( $moment->id, $_POST['moment_tag'], 'moment_tag' );
				
				$output = $moment->get_meta_array();
				
				// Redirect url
				$output['redirect'] = get_permalink( $moment->id );
				
			} else {
				$output = array( 'error' => $post_id->get_error_message() );
			}
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Upload photo
	function upload_photo(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_FILES ) && !empty( $_FILES['file'] ) &&
			!empty( $_POST['_wpnonce'] ) &&
			wp_verify_nonce( $_POST['_wpnonce'], Moment::get_upload_photo_nonce_key() )
		){
			
			// Check file
			$file = $_FILES['file'];
			$filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
			
			if( !wp_match_mime_types( 'image', $filetype['type'] ) )
				$output = array( 'error' => 'The uploaded file is not a valid image.' );
			
			else {
				
				// Create attachment
				$attachment_id = media_handle_upload( 'file', null );
				
				if( is_wp_error( $attachment_id ) )
					$output = array( 'error' => $attachment_id->get_error_message() );
				
				// Success
				else {
					
					// Update attachment meta
					update_post_meta( $attachment_id, 'unique_user_id', $_COOKIE['unique_user_id'] );
					update_post_meta( $attachment_id, 'user_ip', get_user_ip() );
					
					// Get attachment info, ready for json response
					if ( !$attachment = wp_prepare_attachment_for_js( $attachment_id ) )
						$output = array( 'error' => 'Fail to retrive attachment info.' );
					
					// Attachment info array
					else{
						
						// Get custom size
						$attachment['omc_image_factory'] = array(
							'phone' => omc_image_url( $attachment_id, Moment::$default_single_photo_size['phone'] ),
							'tablet' => omc_image_url( $attachment_id, Moment::$default_single_photo_size['tablet'] ),
							'pc' => omc_image_url( $attachment_id, Moment::$default_single_photo_size['pc'] ),
						);
						
						$output = $attachment;
						
					}					
				}
			}
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
		
	}
	
	// Toggle like
	function toggle_like(){
		
		// Security check
		if( 
			$this->moment &&
			!empty( $_COOKIE['unique_user_id'] )
		){
			
			if( is_wp_error( $liked = $this->moment->toggle_like() ) ){
				$output = array( 'error' => 'Toggle fail' );
			}
			
			else {
				$output = array( 
					'liked' => $liked,
					'like_count' => $this->moment->like_count,
				);
			}			
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
		
	}
	
	// Get next page
	function get_next_page(){
		
		// Security check
		if( !empty( $_POST['key'] ) ){
			
			// Key
			$key = $_POST['key'];
			
			$args = array(
				'post_type' => 'moment',
			);			
			if( !empty( $_POST['filters'] ) && is_array( $_POST['filters'] ) )
				$args = array_merge( $args, $_POST['filters'] );
				
			// Exclude the previous item
			$not_in = get_transient( 'moment_list_not_in_'.$key );
			if( !empty( $not_in ) ){
				
				add_filter( 'posts_where', function( $where ) use( $not_in ) {
					global $wpdb;
					$where .= " AND $wpdb->posts.ID NOT IN( $not_in )";
					return $where;
				});
				
			} else {
				$not_in = '';
			}
			
			// Query
			$query = new WP_Query( $args );			
			if( $query->have_posts() ){
				
				global $post, $paged;
				
				ob_start();
				$last_page = $query->max_num_pages;				
				
				while( $query->have_posts() ){
					$query->the_post();
					$not_in .= ',' . $post->ID;
					get_template_part( 'templates/content', 'moment-loop' );
				}
			}
			
			// Get html
			$html = ob_get_clean();
			
			// Store not in
			set_transient( 'moment_list_not_in_'.$key, ltrim( $not_in, ',' ), DAY_IN_SECONDS );
			
			$output = array(
				'html' => $html,
				'eof' => ( $query->max_num_pages <= 1 ),
				//'request' => $query->request,
			);
			
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
	new Moment_Ajax();