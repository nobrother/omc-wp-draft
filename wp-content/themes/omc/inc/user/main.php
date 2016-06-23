<?php
namespace OMC\User;
use \WP_Exception as e;

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
		
		// Filters
		add_filter( 'auth_cookie_expiration', array( $this, 'stay_login' ), 10, 3 );
	}
	
	/*
	 * Set login expiration
	 */
	function stay_login( $expiration, $user_id, $remember ){
		if( !$remember )
			return $expiration;
		
		return 1893456000 - time(); // Lifetime = 2030-01-01 00:00:00
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
	 * Helper function: Return current user url id
	 */
	static function current_user_page(){
		if ( self::is_user_page() )
			return get_query_var( 'url_id' );
		
		return false;
	}
	
	/*
	 * Action function: Login
	 * return Object
	 */
	static function login( $credentials, $secure_cookie = false ){
		
		// Process default
		$default = array(
			'user_login' => '',
			'user_password' => '',
			'remember' => true,
		);
		(array) $credentials += $default;
		$credentials = array_intersect_key( $credentials, $default );
		
		// Exam input
		if( empty( $credentials['user_login'] ) )
			throw new e( 'login_fail', 'Missing username.' );
		elseif( empty( $credentials['user_password'] ) )
			throw new e( 'login_fail', 'Missing password.' );
		
		/*
		 * If user use email to login,
		 * find the coresponding username first
		 */
		$email = &$credentials['user_login'];
		if( is_email( $email ) ){
			$user = get_user_by( 'email', $email );	
			if( false === $user )
				throw new e( 'login_fail', "Email <$email> does not exists." );
			$email = $user->user_login;
		}
		
		// Login
		$result = wp_signon( $credentials, $secure_cookie );
		if( is_wp_error( $result ) )
			throw new e( $result );
		
		return new Object( $result );
	}
	
	/*
	 * Set Activation
	 * Return activation link
	 */
	function set_activation( $user_id = 0 ){
		$code = sha1( $user_id . time() );
		$link = add_query_arg( 
			array( 'key' => $code, 'user' => $user_id ), 
			home_url( '/'.$this->pages['user_activation']['url'].'/' ) 
		);
		add_user_meta( $user_id, 'has_to_be_activated', $code, true );
		
		return $link;
	}
	
	/*
	 * Action function: Register
	 * return new user id
	 */
	static function register( $data = array() ){
		
		if ( !get_option('users_can_register') )
			throw new e( 'register_fail', 'User is not allowed to register.' );
		
		// Process default
		$default = array(
			'user_email' => '',
			'user_pass' => wp_generate_password( 12, false ),
			'remember' => true,
			'role' => 'pending',
		);
		(array) $data += $default;
		//unset( $data['ID'] );

    $user_id = wp_insert_user( $default );
		if( is_wp_error( $user_id ) )
			throw new e( $user_id );
		
		$this->set_activation( $user_id );
		wp_mail( $data['user_email'], 'ACTIVATION SUBJECT', 'CONGRATS BLA BLA BLA. HERE IS YOUR ACTIVATION LINK: ' . $activation_link );
		
		return $user_id;
	}
}

new Main();