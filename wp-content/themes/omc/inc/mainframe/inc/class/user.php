<?php

/*
 * User Class
 */
 
class User{
	
	static $playlist_per_page = 10;
	static $saved_playlist_per_page = 10;
	
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
		elseif( is_int( $id ) )
			$field = 'id';
		else
			$field = 'login';
		
		$user = get_user_by( $field, $id );		
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
		$this->user_login = $this->obj->user_login;
		$this->user_email = $this->obj->user_email;
		$this->display_name = $this->obj->display_name;
		$this->caps = $this->obj->caps;
		$this->cap_key = $this->obj->cap_key;
		$this->roles = $this->obj->roles;
		$this->allcaps = $this->obj->allcaps;
		$this->first_name = $this->obj->first_name;
		$this->last_name = $this->obj->last_name;		
		
		// Default meta
		$default_meta = apply_filters( 'default_user_meta', array(
			'default_playlist' => 0,
		));
		
		// Get meta
		$meta = get_user_meta( $this->id );
		
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
	 * Create new user
	 * Protected
	 */
	static function create( $username = '', $email = '', $password = '' ){
		
		// Set post data
		if( !empty( $username ) )
			$_POST['user_login'] = $username;
		if( !empty( $email ) )
			$_POST['user_email'] = $email;
		if( !empty( $password ) )
			$_POST['user_pass'] = $password;
		
		
		// Action from omc user plugin;
		do_action( 'omc_user_register' );
		
		if( isset( $_POST['omc_user_action_ajax_return'] ) )
			return $_POST['omc_user_action_ajax_return'];
	}
	
	/*
	 * Register nonce key
	 */
	static function register_nonce_key(){
		return 'register_omc_'.$_COOKIE['unique_user_id'].'ohmycode';
	}
	
	/*
	 * Get current playlist video
	 */
	function get_playlists( $args = array() ){
		if( empty( $this->id ) )
			return false;
			
		return self::_get_playlists( $this->id, $args );
	}
	
	/*
	 * Get playlists
	 */
	static function _get_playlists( $user_id = '', $args = array() ){
		
		if( empty( $user_id ) )
			$user_id = get_current_user_id();
		
		$args = wp_parse_args(  $args, array(
			'post_type'   => 'playlist',
			'author' => $user_id,
			'posts_per_page' => self::$playlist_per_page,
			'orderby' => 'menu_order',
			'order' => 'ASC',
		) );
		
		$args = apply_filters( 'user_get_playlists_args', $args );
		return new WP_Query( $args );
	}
	
	/*
	 * Get saved playlists
	 */
	function get_saved_playlists( $args = array() ){
		if( empty( $this->id ) )
			return false;
			
		return self::_get_saved_playlists( $this->id, $args );
	}
	
	/*
	 * Get saved playlists
	 */
	static function _get_saved_playlists( $user_id = '', $args = array() ){
		
		if( empty( $user_id ) )
			$user_id = get_current_user_id();
		
		$args = wp_parse_args(  $args, array(
			'post_type'   => 'saved_playlist',
			'author' => $user_id,
			'posts_per_page' => self::$saved_playlist_per_page,
			'orderby' => 'menu_order',
			'order' => 'DESC',
		) );
		
		$args = apply_filters( 'user_get_saved_playlists_args', $args );
		return new WP_Query( $args );
	}
}

/*
 * Ajax Class
 */ 
class User_Ajax{
	
	protected $action_prefix = 'lm_user_';
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		// Load user if any
		if( !empty( $_POST['uid'] ) ){
			$this->user = new user( (int) $_POST['uid'] );
			if( empty( $this->user ) )
				$this->user = false;
		}
		else
			$this->user = false;
		
		// Add action hook
		$this->add_ajax( 'register_user' );
		$this->add_ajax( 'check_username_exists' );
		$this->add_ajax( 'check_email_exists' );
		$this->add_ajax( 'load_playlists_page' );
	}
	
	// Load playlists page
	function load_playlists_page(){
		
		// Security check
		if( 
			$this->user &&
			!empty( $_POST['key'] ) &&
			( $key = $_POST['key'] ) &&
			isset( $_POST['listTemplate'] ) &&
			( $template = $_POST['listTemplate'] ) &&
			!empty( $_POST['filters'] ) && 
			( $args = $_POST['filters'] ) &&
			!empty( $_POST['paged'] ) && 
			( $paged = $_POST['paged'] ) &&
			!empty( $args['post_type'] ) && 
			$args['post_type'] == 'playlist'			
		){
			
			if( $not_in = get_transient( 'pis_not_in_'.$key ) ){
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
			$query = $this->user->get_playlists( $args );			
			if( $query->have_posts() ){
				
				global $post;
				
				ob_start();		
				
				$playlists = array();
				
				while( $query->have_posts() ){
					$query->the_post();
					
					$playlist = new Playlist( $post );
					$playlists[] = $playlist->get_meta_array();
					
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
				'items' => $playlists,
			);
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		//$output['post'] = $_POST;
		//$output['user'] = $this->user->id;

		$this->json_headers();
		echo json_encode( $output );
		
		die();
	}
	
	// Check email exists
	function check_email_exists(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['value'] ) &&
			!empty( $_POST['_wpnonce'] ) &&
			wp_verify_nonce( $_POST['_wpnonce'], User::register_nonce_key() )
		){
			
			if( !is_email( $_POST['value'] ) )
				$output = array( 'valid' => false, 'message' => sprintf( "Email '%s' is not valid.", $_POST['value'] ) );
			elseif( email_exists( $_POST['value'] ) )
				$output = array( 'valid' => false, 'message' => sprintf( "Email '%s' had been used.", $_POST['value'] ) );
			else
				$output = array( 'valid' => true );
			
		} else {
			$output = array( 'error' => 'Access denied.', 'valid' => false, 'message' => 'Are you try to hack me?' );
		}

		$this->json_headers();
		echo json_encode( $output );

		die();
	}
	
	// Check username exists
	function check_username_exists(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['value'] ) &&
			!empty( $_POST['_wpnonce'] ) &&
			wp_verify_nonce( $_POST['_wpnonce'], User::register_nonce_key() )
		){
			
			if( username_exists( $_POST['value'] ) )
				$output = array( 'valid' => false, 'message' => sprintf( "Username '%s' had been used.", $_POST['value'] ) );
			else
				$output = array( 'valid' => true );
			
		} else {
			$output = array( 'error' => 'Access denied.', 'valid' => false, 'message' => 'Are you try to hack me?' );
		}

		$this->json_headers();
		echo json_encode( $output );

		die();
	}
	
	// Register user
	function register_user(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['user_login'] ) &&
			!empty( $_POST['user_email'] ) &&
			!empty( $_POST['user_pass'] ) &&
			!empty( $_POST['_wpnonce'] ) &&
			wp_verify_nonce( $_POST['_wpnonce'], User::register_nonce_key() )
		){
			
			// Create user
			User::create();
			
			// Get result
			if( !empty( $_POST['omc_user_action_ajax_return'] ) )			
				$output = $_POST['omc_user_action_ajax_return'];
			
			else
				$output = array( 'error' => 'Cannot perform register action!' );
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		//$output['post'] = $_POST;
		
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
	new User_Ajax();