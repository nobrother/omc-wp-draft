<?php 

if ( ! defined( 'ABSPATH' ) ) {
	die(); // Exit if accessed directly
}

/**
 * OMC User
 *
 * Main class for the plugin
 *
 * @class 		OMC_User
 * @version		1.0.0
 * @plugin		omc-user
 * @category	Class
 * @author 		Chang
 */ 
 
class OMC_User {
	
	/**
	 * Page slugs lists
	 */
	public static $page_slugs = array(
		'activation'	=> 'activation',
		'error'	=> 'user-error',
		'forgotpassword'	=> 'forgot-password',
		'login'	=> 'login',
		'profile'	=> 'edit-profile',
		'register'	=> 'register',
		'action' => 'user-action',
	);
	
	public static $default_profile_photo = 'http://www.fillmurray.com/120/120';
	
	public static $default_meta = array(
		'profile_photo' => 0,
	);
	
	/**
	 * Construct
	 */
	public function __construct() {
		
		/**
		 * Inits the plugins, loads all the files
		 * and registers all actions and filters
		 */

  	// This is for the Heartbeat API and auth check.
		// If auth check is failed, the user will be promted to login again.
		// It loads wp_login_url() in a iframe. Currently the user-login.php is
		// not optimized for this iframe, so lets just link the login URL, which opens
		// in a new tab. Fore more, see wp_auth_check_html()
		add_action( 'wp_auth_check_same_domain', '__return_false' );

		// set up general hook
		add_action( 'omc_user_set_request_vars', array( $this, 'set_request_vars' ) );
		add_action( 'omc_user_get_request_vars', array( $this, 'get_request_vars' ) );
		add_action( 'lostpassword_url', array( $this, 'lostpassword_url' ) );
		add_action( 'register_url', array( $this, 'register_url' ) );
		add_action( 'wp_signup_location', array( $this, 'signup_location' ) );
		add_action( 'login_url', array( $this, 'login_url' ), 10, 2 );
		add_action( 'logout_url', array( $this, 'logout_url' ), 10, 2 );
		add_action( 'edit_profile_url', array( $this, 'edit_profile_url' ) );
		add_action( 'personal_options_update', array( $this, 'save_user_meta' ) );

		// restrict the backend access
		add_action( 'show_admin_bar', array( $this, 'maybe_remove_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'maybe_block_backend' ) );

		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Template includes
		add_action( 'template_include', array( $this, 'template_include' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		
		// Media Uploader
		
		add_filter( 'ajax_query_attachments_args', array( $this, 'users_own_attachments' ) );
		
		// Action handler
		require_once OMC_USER_CLASS_DIR.'/class-omc-user-action.php';
		new OMC_User_Action();
		
		// wp titles
		add_action( 'wp_title', array( $this, 'wp_title' ), 10, 3 );		
	}
	
	/**
	 * Get default meta
	 */	
	static function get_default_meta(){
		return apply_filters( 'default_omc_user_meta', self::$default_meta );
	}
	
	/**
	 * Get user
	 */
	static function get( $id = 0 ){
		
		if( empty( $id ) && empty( $id = get_current_user_id() ) )
			return false;
			
		$user = get_userdata( $id );
		
		if( empty( $user ) )
			return false;
		
		// Default meta
		$default_meta = self::get_default_meta();
		
		// Get meta
		$meta = get_user_meta( $id );
		
		// Populate meta
		foreach( $default_meta as $key => $value ){
			if( isset( $meta[$key] ) ){
				
				// Follow default meta format: is scalar or not
				if( is_scalar( $value ) )
					$user->$key = array_shift( $meta[$key] );
				else
					$user->$key = maybe_unserialize( $meta[$key] );
			}
			
			// Use default
			else
				$user->$key = $value;
		}
		
		return $user;		
		
	}
	
	/**
	 * Check if it is frontend
	 */
	static function is_frontend(){
		$adminurl = strtolower( admin_url() );
		$referer = strtolower( wp_get_referer() );
		return strpos( $referer, $adminurl ) === 0;
	}
	
	
	/**
	 * Save user meta
	 */
	function save_user_meta( $user_id ){
		
		// Default meta
		$default_meta = self::get_default_meta();
		foreach( $default_meta as $meta_key => $meta_value ){
			if( isset( $_POST[$meta_key] ) )
				update_user_meta( $user_id, $meta_key, $_POST[$meta_key] );
		}		
	}
	
	/**
	 * Filter attachement by author
	 */
	function users_own_attachments( $args ) {

			global $current_user, $pagenow;
			
			if( is_user_logged_in() && self::is_frontend() )
				$args['author'] = get_current_user_id();

			return $args;
	}
	
	/**
	 * Load media uploader
	 */
	static function load_media_uploader(){
		
		// Allow image only
		add_filter('upload_mimes', function( $mime ){
			return array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'tif|tiff' => 'image/tiff'
			);
		});
		
		// Customize UI - tab captions
		add_filter( 'media_view_strings', function( $strings ){
			$strings['mediaLibraryTitle'] = 'Your profile photo';
			return $strings;
		});
		
		wp_enqueue_media();
		
	}
	
	/**
	 * loads the action url for the forms
	 */
	public function get_action_url( $action ) {
		return home_url( '/'.self::$page_slugs['action'].'/?action=' . $action );
	}

	/**
	 * standard login form arguments
	 */
	public function login_form_args() {
		$args = array(
			'echo'				=> TRUE,
			'redirect'			=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Default redirect is back to the current page
			'form_id'			=> 'loginform',
			'label_username'	=> 'Username',
			'label_password'	=> 'Password',
			'label_remember'	=> 'Remember Me',
			'label_log_in'		=> 'Log In',
			'id_username'		=> 'user_login',
			'id_password'		=> 'user_pass',
			'id_remember'		=> 'rememberme',
			'id_submit'			=> 'wp-submit',
			'remember'			=> true,
			'value_username'	=> '',
			'value_remember'	=> false, // Set this to true to default the "Remember me" checkbox to checked
		);

		return apply_filters( 'omc_login_form_args', $args );
	}

	/**
	 * on submit, we have to store the post and get
	 * vars in session to get them back after redirect
	 */
	public function set_request_vars(){

		$this->maybe_start_session();

		if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' )
			$_SESSION[ 'omc_user_post_vars' ] = $_POST;

		// on a get- and/or post-request, you can send GET-Params
		$_SESSION[ 'omc_user_get_vars' ] = $_GET;
	}

	/**
	 * when loading the template, we have to refetch
	 * after Redirect the POST and GET-vars from Session
	 */
	public function get_request_vars(){
		
		$this->maybe_start_session();

		// trying to get the POST-vars session
		if ( array_key_exists( 'omc_user_post_vars', $_SESSION ) )
			$_POST = array_merge( $_SESSION[ 'omc_user_post_vars' ], $_POST );

		// trying to get the GET-Vars from session
		if ( array_key_exists( 'omc_user_get_vars', $_SESSION ) )
			$_GET = array_merge( $_SESSION[ 'omc_user_get_vars' ], $_GET );

		// now we have to combine the GET, POST and REQUEST to rebuild the old $_REQUEST
		$_REQUEST =  array_merge( $_GET, $_POST, $_REQUEST );
		
		
	}

	/**
	 * wordpress-style function to start the session when net started.
	 */
	public function maybe_start_session(){
		if ( ! session_id() )
			session_start();
	}

	/**
	 * Get the lost password url
	 */
	public function lostpassword_url( $url ) {
		return home_url( '/'.self::$page_slugs['forgotpassword'].'/' );
	}

	/**
	 * Get the register url
	 */
	public function register_url( $url ) {
		return home_url( '/'.self::$page_slugs['register'].'/' );
	}

	/**
	 * Get the register url
	 */
	public function signup_location( $url ) {
		return home_url( '/'.self::$page_slugs['register'].'/' );
	}

	/**
	 * Get the login url
	 */
	public function login_url( $url, $redirect = '' ) {
		$login_url = home_url( '/'.self::$page_slugs['login'].'/' );

		if ( ! empty( $redirect ) )
			$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );

		return $login_url;
	}

	/**
	 * Get the logout url
	 */
	public function logout_url( $url, $redirect = '' ) {

		$args = array( 'action' => 'logout' );
		if ( ! empty( $redirect ) )
			$args[ 'redirect_to' ] = urlencode( $redirect );

		$logout_url = add_query_arg( $args, home_url( '/' ) );
		$logout_url = str_replace( '&amp;', '&', $logout_url );
		$logout_url = add_query_arg( '_wpnonce', wp_create_nonce( 'logout' ), $logout_url );
		$logout_url = esc_html( $logout_url );

		return $logout_url;
	}

	/**
	 * Get the edit profile url
	 */
	public function edit_profile_url( $url ){
		if ( is_admin() )
			return $url;

		return home_url( '/'.self::$page_slugs['profile'].'/' );
	}
	
	/**
	 * Determinates if a user has the
	 * capabilities to see the admin bar.
	 */
	public function maybe_remove_admin_bar() {
		return false;
		return current_user_can( 'edit_posts' );
	}

	/**
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
	
	/**
	 * Adds the needed javascript in the frontend
	 */
	public function enqueue_scripts(){
		/*
		$suffix = defined( 'WP_DEV' ) && WP_DEV ? '' : '.min';
		$path = defined( 'WP_DEV' ) && WP_DEV ? '' : 'min/';
		
		wp_enqueue_script(
			'omc-user',
			OMC_USER_JS_URL . "/{$path}password{$suffix}.js",
			array( 'jquery', 'utils' ),
			null, 
			true
		);
		
		wp_localize_script( 'omc-user', 'omc_user_vars', array(
			'strength_indicator' => 'Strength indicator',
			'very_weak' => 'Very weak',
			'weak' => 'Weak',
			'medium' => 'Medium',
			'strong' => 'Strong',
			'mismatch' => 'Mismatch'
		) );
		*/
	}
	
	/**
	 * Checks which template should be included
	 */
	public function template_include( $template ) {
		
		// check if we have an action or a template
		if ( get_query_var( 'omc_user' ) == self::$page_slugs['action'] )
			return;
		
		$template_filename = apply_filters( 'omc-user-template-filename', 'omc-user-' . array_search( get_query_var( 'omc_user' ), self::$page_slugs ), get_query_var( 'omc_user' ) );
		
		$user_template = get_template_directory() . '/templates/' . $template_filename . '.php';
		$new_template = OMC_USER_TEMPLATE_DIR . '/' . $template_filename . '.php';
		
		if ( file_exists( $user_template ) ) {
			$template   = $user_template;
			
			// Rebuild the request_vars after
			// the redirect to wp-load.php on our custom
			// login-, register, logout-stuff
			do_action( 'omc_user_get_request_vars' );
			
		} else if ( file_exists( $new_template ) ) {
			$template   = $new_template;
			
			// Same as above
			do_action( 'omc_user_get_request_vars' );
		}

		return $template;
	}

	/**
	 * Rewrite the permalinks
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		
		$rules = array();
		
		foreach( self::$page_slugs as $key => $value ){
			if( $key == 'activation' ){
				// Special for activation
				$rules[$value.'/?([A-Za-z0-9-_.,]+)?'] = 'index.php?omc_user='.$value.'&key=$matches[1]';
			} else {
				$rules[$value] = "index.php?omc_user={$value}";
			}
		}

		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
		
	}

	/**
	 * query var registration
	 */
	public function query_vars( $qvars ){
		$qvars[] = 'omc_user';
		return $qvars;
	}

	/**
	 * Set is_home to false and no robots tag
	 */
	public function template_redirect() {
		global $wp_query;
		
		if ( $wp_query->get( 'omc_user' ) ) {
			$wp_query->is_home = false; // Set is_home parameter to false
			add_action( 'wp_head', 'wp_no_robots' );
		}
	}
	
	/**
	 * Filter the text of the page title.
	 */
	public function wp_title( $title, $sep, $seplocation ) {

		// get current url
		$current_action = get_query_var( 'omc_user' );
		if ( ! $current_action )
			return $title;

		// switch the title
		switch ( $current_action ) {
			case self::$page_slugs['login']:
				$title = apply_filters( 'omc_user_wp_title_user_login', 'Login' );
				break;
			case self::$page_slugs['profile']:
				$title = apply_filters( 'omc_user_wp_title_user_edit_profile', 'Edit profile' );
				break;
			case self::$page_slugs['register']:
				$title = apply_filters( 'omc_user_wp_title_user_register', 'Register' );
				break;
			case self::$page_slugs['reset_password']:
				$title = apply_filters( 'omc_user_wp_title_user_reset_password', 'Reset Password' );
				break;
			case self::$page_slugs['forgot_password']:
				$title = apply_filters( 'omc_user_wp_title_user_forgot_password', 'Forgot Password' );
				break;
			case self::$page_slugs['activation']:
				$title = apply_filters( 'omc_user_wp_title_user_activation', 'Activation' );
				break;
			default:
				$title = $title;
				break;
		}

		// set the tempory seperator
		$t_sep = '%WP_TITILE_SEP%'; // Temporary separator, for accurate flipping, if necessary

		// set prefix
		$prefix = '';
		if ( !empty( $title ) )
			$prefix = " $sep ";

		/**
		 * Filter the parts of the page title.
		 *
		 * @since 4.0.0
		 *
		 * @param array $title_array Parts of the page title.
		 */
		$title_array = apply_filters( 'wp_title_parts', explode( $t_sep, $title ) );

		// Determines position of the separator and direction of the breadcrumb
		if ( 'right' == $seplocation ) { // sep on right, so reverse the order
			$title_array = array_reverse( $title_array );
			$title = implode( " $sep ", $title_array ) . $prefix . get_bloginfo('name');
		} else {
			$title = get_bloginfo('name') . $prefix . implode( " $sep ", $title_array );
		}

		return $title;
	}
	
	// Redirect to login
	static function go_login(){
		wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['login'].'/' ) );
	}
}