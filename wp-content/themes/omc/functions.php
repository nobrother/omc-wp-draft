<?php
/**
 * omc framework.
 * 
 * @theme omc
 * @version 2.00
 * @author  Chang
 */
//var_dump($_SERVER);

//* Run the omc_pre Hook
do_action( 'omc_pre' );


// Timezone
global $timezone;
$timezone = new DateTimeZone( timezone_name_from_abbr( '', get_option('gmt_offset') * HOUR_IN_SECONDS, 0 ) );

/******************
 * Configuration
 ******************/
require_once 'config.php';
global $theme_config;

// Load for general
foreach( $theme_config['loader']['general'] as $file )
	require_once $file;

// Load for frontend
if( !is_admin()	|| omc_is_ajax() === 'front' ){
	foreach( $theme_config['loader']['frontend'] as $file )
		require_once $file;
}

// Load for backend
if( is_admin() && omc_is_ajax() !== 'front' ) {
	foreach( $theme_config['loader']['backend'] as $file )
		require_once $file;
}
if( is_omc_ajax() ){
	foreach( $theme_config['loader']['ajax'] as $file )
		require_once $file;
}
unset( $theme_config );
 
 

/******************
 * LOAD  FRAMEWORK & SETUP
 ******************/

/**
 * Setup basic blog settings
 */
add_action( 'after_setup_theme', 'omc_blog_setup' );
function omc_blog_setup() {

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 0, true );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
		'gallery',
		'status',
		'audio',
		'chat',
	) );
}

/**
 * Register menu location
 */
add_action( 'omc_init', 'omc_menu_location' );
function omc_menu_location() {
	register_nav_menus( array( 'top-right' => 'Top Right' ) );
	add_filter('nav_menu_css_class' , function( $classes, $item ){
		if( in_array( 'current-menu-item', $classes ) )
			$classes[] = 'active ';
    return $classes;
	},10 ,2 );
}

/**
 * Loads all the framework files and features.
 */
add_action( 'omc_init', 'omc_load_framework' );
function omc_load_framework() {
	main();	
}


/*******************
 * DISABLE DEFAULT
 *******************/
 
/**
 * Disable pingback
 */
add_filter( 'xmlrpc_methods', 'omc_block_xmlrpc_attacks' );
add_filter( 'wp_headers', 'omc_disable_pingback' );
function omc_disable_pingback( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
function omc_block_xmlrpc_attacks( $methods ){
	unset( $methods['pingback.ping'] );
	unset( $methods['pingback.extensions.getPingbacks'] );
	return $methods;
}

/**
 * [REMOVE] Disable rich editing or visual editor
 * 
 * Refer to wp function user_can_richedit() at /wp-include/genaral-template.php line 2376
 */
//add_filter( 'user_can_richedit', 'omc_disable_rich_editing' );
function omc_disable_rich_editing( $wp_rich_edit ){
	// 'disable_rich_editing' option is set in theme setting page
	if( omc_get_option( 'disable_rich_editing' ) )
		return false;
	return $wp_rich_edit;
}

/*
 * Set Unique user cookies
 * Login type and annonymous type
 */
add_action( 'init', 'omc_cookie_routine' );
function omc_cookie_routine(){
	require_once OMC_APPS_DIR.'/cookie/cookie.php';
	
	if( is_user_logged_in() ){
		$user_hash = md5( get_current_user_id() );
		Cookie::Set('login_user_id', $user_hash, Cookie::Lifetime, '/' );
	} 
	
	elseif( !isset( $_COOKIE['annonymous_user_id'] ) ){
		$user_hash = md5( rand() . uniqid() . rand() );
		Cookie::Set('annonymous_user_id', $user_hash, Cookie::Lifetime, '/' );
	}
	
	if( isset( $user_hash ) )
		Cookie::Set('unique_user_id', $user_hash, Cookie::Lifetime, '/' );
}

/**
 * Register widget area.
 */
function omc_widgets_init() {
	register_sidebar( array(
		'name'          => 'Blog right sidebar',
		'id'            => 'blog-right-sidebar-widget-area',
		'description'		=> 'At the right sidebar on blog list',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<div class="widget-title">',
		'after_title'   => '</div>',
	) );
}
add_action( 'widgets_init', 'omc_widgets_init' );

function omc_register_cpt_tax(){
	// Register CPT
	omc_cpt( 'portfolio', 'Portfolio', 'Portfolios' )
		->set_args( 'supports', array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt', 'custom-fields' ) )
		->set_args( 'menu_icon', 'dashicons-archive' )
		->register();
}
add_action( 'omc_initiated', 'omc_register_cpt_tax' );

/**
 * omc framework initiates here
 */
do_action( 'omc_init' );

/**
 * All omc framework is initiated
 */
do_action( 'omc_initiated' );

// Start session
maybe_start_session();

/*
 * Choose template to loader
 * Apply to filter '{$type}_template'
 * Possible values for `$type` include: 'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date',
 * 'home', 'front_page', 'page', 'paged', 'search', 'single', 'singular', and 'attachment'.
 */
$template_types = array(
	'index', 
	'404', 
	'archive', 
	'author', 
	'category', 
	'tag', 
	'taxonomy', 
	'date', 
	'home', 
	'front_page', 
	'page', 
	'paged', 
	'search', 
	'single', 
	'singular', 
	'attachment',
	'comments',
);
foreach( $template_types as $type ){
	add_filter( $type.'_template', 'omc_template_loader' ); 
}
unset( $template_types );


/**
 * Contact Form 7
 */
//add_filter( 'wpcf7_load_js', '__return_false' );	// Disable JS
//add_filter( 'wpcf7_load_css', '__return_false' ); // Disable CSS