<?php
/**
 * OMC Mainframe
 * 
 * This class is aspect to be heavily change every project
 * Everything on the current project goes here
 * It should contain it's own constants, functions and class etc.
 * Make the class with all static method and properties.
 */
class OMC_Mainframe {
	
	protected static $instance = null;
	
	/**
	 * Construct
	 */
	function __construct() {
		
		// Cannot create more than one instance
		if ( !is_null( self::$instance ) )
			return false;
			
		// Hook
		add_action( 'omc_initiated', array( $this, 'register_constants' ) );
		//add_action( 'omc_initiated', array( $this, 'register_classes' ) );
		//add_action( 'omc_initiated', array( $this, 'create_cpt' ) );
		
		//add_action( 'init', array( $this, 'pre_load_classes' ) );
		
		// Search
		//add_filter( 'init', array( $this, 'add_post_types_query_var' ) );
		//add_filter( 'search_rewrite_rules', array( $this, 'search_rewrite_rules' ) );
		//add_filter( 'search_link', array( $this, 'search_link' ) );
		//add_action( 'template_redirect', array( $this, 'search_redirect' ) );
	}
	
	/*
	 * add `post_types` to query vars
	 */
	function add_post_types_query_var(){
		add_rewrite_tag( '%post_types%', '([^/]+)' );
	}
	
	/*
	 * Search permalink
	 */
	function search_rewrite_rules( $search_rewrite ){
	
		global $wp_rewrite;
		
		$search_rewrite = $wp_rewrite->generate_rewrite_rules( 'search/%search%/what/%post_types%', EP_SEARCH );
		
    return $search_rewrite;
	}
	function search_link( $link ){
		
		// Check post_types
		if( ( $post_types = get_query_var( 'post_types' ) ) && empty( $post_types ) )
			return $link;
			
		$link .= 'what/' . $post_types;
		
		return $link;
	}
	
	/*
	 * Search redirect
	 */
	function search_redirect(){
		
		global $wp_rewrite;
		
		// Test
		if ( 
			!is_search() ||
			is_admin() ||
			empty( $_GET['s'] ) ||
			!isset( $wp_rewrite ) || 
			!is_object( $wp_rewrite ) || 
			!$wp_rewrite->using_permalinks()
		)
			return;
		
		wp_redirect( get_search_link() );
		
		die();
	}
	
	/*
	 * Register Constants
	 */
	function register_constants(){		
		define( 'MAIN_INC_PATH', __DIR__ . '/inc' );
		define( 'MAIN_CLASS_PATH', MAIN_INC_PATH . '/class' );
		define( 'MAIN_COOKIE_PATH', MAIN_INC_PATH . '/cookies' );
	}
	
	/*
	 * Register Classes
	 */
	function register_classes(){
		
		// Set include path
		//set_include_path( MAIN_CLASS_PATH );
		
		$default_classes = array(
			'user' => 'user',
			'moment' => 'moment',
			'spr' => 'spr',
		);
		
		$this->registered_classes = apply_filters( 'main_registered_classes', $default_classes );
	}
	
	/*
	 * Pre Load Classes
	 */
	function pre_load_classes(){		
		$pre_load_classes = apply_filters( 'pre_load_classes', array(	'user', 'moment', 'spr' ) );		
		load_classes( $pre_load_classes ); 
	}
	
	
	/*
	 * Register CPT
	 */
	function create_cpt(){
		// Register tag
		omc_taxonomy( 'moment_tag', 'moment', 'Moment Tag', 'Moment Tags' )
			->register();
			
		// Register category
		omc_taxonomy( 'moment_event', 'moment', 'Moment Event', 'Moment Events' )
			->set_args( 'hierarchical', true )
			->set_args( 'rewrite', array( 'hierarchical' => true ) )
			->register();
			
		// Register category
		omc_taxonomy( 'moment_type', 'moment', 'Moment Type', 'Moment Type' )
			->set_args( 'hierarchical', true )
			->set_args( 'rewrite', array( 'hierarchical' => true ) )
			->register();
			
		// Register category
		omc_taxonomy( 'moment_location', 'moment', 'Moment Location', 'Moment Locations' )
			->set_args( 'hierarchical', true )
			->set_args( 'rewrite', array( 'hierarchical' => true ) )
			->register();
		
		// Register CPT
		omc_cpt( 'moment', 'Moment', 'Moments' )
			->set_args( 'supports', array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt', 'custom-fields' ) )
			->set_args( 'menu_icon', 'dashicons-camera' )
			->register();
	}
	
	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, 'No cheating.' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, 'No cheating.' );
	}
	
	/**
	 * Mainframe Instance
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 */
	static function get_instance() {
		if ( is_null( self::$instance ) )
			self::$instance = new self();
			
		return self::$instance;
	}
	
}

/**
 * Function to manage mainframe object
 * Please use this function instead of direct access
 */
function main(){		
	return OMC_Mainframe::get_instance();
}

/**
 * Function to load class
 */
function load_classes( $classes ){		
	
	if( empty( $classes ) )
		return false;
		
	if( !defined( 'MAIN_CLASS_PATH' ) )
		return false;
	
	// Force $classes to array
	if( !is_array( $classes ) )
		$classes = (array) $classes;
	
	// Retrive registed classes
	$registered_classes = main()->registered_classes;
	if( empty( $registered_classes ) )
		return false;
	
	// Loop
	foreach( $classes as $class ){
		if( !isset( $registered_classes[$class] ) )
			continue;
			
		$filename = MAIN_CLASS_PATH . '/' .$registered_classes[$class] . '.php';
		if( !file_exists( $filename ) )
			continue;
			
		require_once( $filename );
	}
	
}

do_action( 'omc_mainframe_init' );
