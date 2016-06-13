<?php
namespace OMC\User;

class Main{
	
	protected $pages = array();
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		/**
		 * Page info
		 */
		$this->pages = array(
			'user_login' => array(
				'url' => 'login',
			),
			'user_logout' => array(
				'url' => 'logout',
			),
			'user_register' => array(
				'url' => 'register',
			),
			'user_lostpassword' => array(
				'url' => 'lostpassword',
			),
			'user_activation' => array(
				'url' => 'activation',
			),
			'user_edit_profile' => array(
				'url' => 'account',
			),
			'user_edit_password' => array(
				'url' => 'account/password',
			),
		);
		
		// Actions/Filters
		add_filter( 'omc_custom_urls', array( $this, 'register_custom_urls' ) );
		
		// restrict the backend access
		add_action( 'show_admin_bar', array( $this, 'maybe_show_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'maybe_block_backend' ) );
		
		// wp titles
		add_action( 'wp_title', array( $this, 'wp_title' ) );
		
		// set up general hook
		add_action( 'lostpassword_url', array( $this, 'lostpassword_url' ) );
		add_action( 'register_url', array( $this, 'register_url' ) );
		add_action( 'login_url', array( $this, 'login_url' ), 10, 2 );
		add_action( 'logout_url', array( $this, 'logout_url' ), 10, 2 );
		add_action( 'edit_profile_url', array( $this, 'edit_profile_url' ) );
	}
	
	/*
	 * Register custom_urls
	 */
	function register_custom_urls( $urls ){
	
		foreach( $this->pages as $page_id => $info ){
			$urls[$page_id] = array(
				'url' => $info['url'],
				'group' => 'omc_user',
				'template' => OMC_TEMPLATE_DIR.'/user/index.php',
			);
		}
		return $urls;
	}
	
	/*
	 * Filter: Login url
	 */
	function login_url( $login_url, $redirect ){
		return add_query_arg( 
			'redirect_to',
			$redirect,
			home_url( '/'.$this->pages['user_login']['url'].'/' ) 
		);
	}
	
	/*
	 * Filter: Logout url
	 */
	function logout_url( $logout_url, $redirect ){
		return add_query_arg( 
			'redirect_to',
			$redirect,
			home_url( '/'.$this->pages['user_logout']['url'].'/' )
		);
	}
	
	/*
	 * Filter: Register url
	 */
	function register_url( $register_url ){
		return home_url( '/'.$this->pages['user_register']['url'].'/' );
	}
	
	/*
	 * Filter: Lost password url
	 */
	function lostpassword_url( $lostpassword_url ){
		return home_url( '/'.$this->pages['user_lostpassword']['url'].'/' );
	}
	
	/*
	 * Filter: Edit profile url
	 */
	function edit_profile_url( $edit_profile_url ){
		return home_url( '/'.$this->pages['user_edit_profile']['url'].'/' );
	}
	
	/**
	 * Determinates if a user has the
	 * capabilities to see the admin bar.
	 */
	public function maybe_show_admin_bar( $is_show ) {
		return current_user_can( 'edit_posts' ) && $is_show;
	}
	
	/*
	 * Checks if the current request should be
	 * redirected to the frontend.
	 */
	function maybe_block_backend() {
		
		// If no user login
		if( !is_user_logged_in() )
			return;
		
		// If user can edit posts do nothing
		if ( current_user_can( 'edit_posts' ) )
			return;

		// AJAX requests via wp-admin/admin-ajax.php needs to be passed
		if ( defined( 'DOING_AJAX') && DOING_AJAX )
			return;

		// Don't redirect when WP CLI is active. This prevents some notices
		// by WP CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI )
			return;

		wp_safe_redirect( home_url() );
		exit;
	}
	
	/*
	 * Manage page title
	 */
	function wp_title( $title ){
		if ( get_query_var( 'omc_custom_page' ) != 1 )
			return $title;
		
		if( get_query_var( 'title' ) )
			return get_query_var( 'title' );
		
		if( get_query_var( 'user_page' ) )
			return get_query_var( 'title' );
			
		return $title;
	}
	
	/*
	 * Helper function: Check if this is user page
	 * $page can be login, logout...
	 */
	static function is_user_page( $page = '' ){
		return get_query_var( 'omc_custom_page' ) == 1 && 
			get_query_var( 'group' ) === 'omc_user' &&
			$page ? self::current_user_page() == $page : true;
	}
	
	/*
	 * Helper function
	 */
	static function current_user_page(){
		if ( self::is_user_page() )
			return get_query_var( 'url_id' );
		
		return false;
	}
}

/*
 * User Class
 */
 
class User{
	
	const USERNAME_SALT = '91u~+ qiJN&%!nas nu';
	
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
	 * Create video
	 */
	function create( $playlist_id = 0, $meta = array(), $args = array() ){
		$id = self::_create_or_update( $playlist_id, $meta, $args );
		
		// Get post object and populate
		if( $id ){
			$post = get_post( $id );
			$this->populate( $post );
		}
		
	}
	
	/*
	 * Generate user name
	 */
	static function gen_username( $ic = '' ){
		return md5( 'omc6gst'.$ic.self::USERNAME_SALT );
	}
	
}

/*
 * Ajax Class
 */ 
class User_Ajax{
	
	protected $action_prefix = 'at_user_';
	
	/*
	 * Contruct
	 */
	function __construct(){
		
		// Load user if any
		if( !empty( $_POST['uid'] ) ){
			$this->user = new user( $_POST['uid'] );
			if( empty( $this->user ) )
				$this->user = false;
		}
		else
			$this->user = false;
		
		// Add action hook
		$this->add_ajax( 'register_check_spr' );
		$this->add_ajax( 'register_user' );
		
	}
	
	// Register user
	function register_user(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['user_email'] ) &&
			!empty( $_POST['user_pass'] ) &&
			!empty( $_POST['sprResult'] ) &&
			!empty( $_POST['sprResult']['ic'] ) //&&
			//!empty( $_POST['_wpnonce'] ) &&
			//wp_verify_nonce( $_POST['_wpnonce'], 'register-step-4' )
		){
			
			// Create username
			$ic = $_POST['sprResult']['ic'];
			$_POST['user_login'] = User::gen_username( $ic );
			
			// Action from omc user plugin;
			do_action( 'omc_user_register' );
			
			// Get result
			if( !empty( $_POST['omc_register_user_ajax_return'] ) ){
			
				$result = $_POST['omc_register_user_ajax_return'];
				
				// Error
				if( is_wp_error( $result ) )
					$output = array( 'error' => $result->get_error_message() );
				
				// Success
				else {
					$output = array( 
						'status' => 'success',
						'redirect' => get_edit_user_link(),
					);
				}
			}
			
			else
				$output = array( 'error' => 'Cannot perform register action!' );
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		
		$output['post'] = $_POST;
		$this->json_headers();
		echo json_encode( $output );
		
		die();
		
	}
	
	// Check SPR
	function register_check_spr(){
		
		// Security check
		if( 
			!empty( $_COOKIE['unique_user_id'] ) &&
			!empty( $_POST['user_ic'] ) &&
			!empty( $_POST['_wpnonce'] ) &&
			wp_verify_nonce( $_POST['_wpnonce'], 'register-step-1' )
		){
			
			$ic = $_POST['user_ic'];
			
			// Check if the ic had been use
			$username = User::gen_username( $ic );			
			if( username_exists( $username ) )
				$output = array( 'error' => 'Someone had use the IC!' );
				
			// Request
			elseif( is_wp_exception( $result = spr()->init()->single_check( $ic ) ) )
				$output = array( 'error' => $result->get_error_message() );
			
			// Process result
			else {
				$output = array( 'result' => $result );
				
				// Build SPR info table
				ob_start();
				
				if( is_mobile() )
					get_template_part( 'templates/content-user-spr-table', 'mobile' );
				else
					get_template_part( 'templates/content-user-spr-table', 'pc' );
				
				$output['html'] = ob_get_clean();
				
			};
			
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

new Main();

if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
	new User_Ajax();
