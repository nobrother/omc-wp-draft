<?php

namespace OMC\Page;
use OMC\Post_Object_Custom_Settings;
use OMC\Post_Object;
use OMC\Post_Object_Ajax;
use \WP_Exception as WP_Exception;

/*
 * Custom settings
 */
class Custom_Settings extends Post_Object_Custom_Settings {
	
	public $post_type = 'page';
	public $default = array( 
		'stylesheet' => '',
	);
	public $sanitize_type = array( 
		'stylesheet' => 'sanitize_title',
	);
}

/*
 * Main
 */
class Page extends Post_Object {
	
	// Define variable
	protected $name = 'page';
	const HASH = 'asdasfafadge'; 
	
	
	/*
	 * Construct
	 */
	function __construct( $id = 0 ){
		
		parent::__construct( $id );
		
		// Define sample
		$this->samples[OMC_TEMPLATE_DIR.'/page/'.'##slug##'] = array(
			'page-layout.php' => 'layout.php', 
			'page-content.php' => 'content.php', 
			'page-style.less' => 'style.less',
		);
		
		// Register custom settings
		$this->custom_settings = new Custom_Settings();			
	}
	
	/*
	 * Populate post object
	 * $obj is WP_Post
	 */
	function populate( $obj = '' ){
		parent::populate( $obj );
		
		// Register placeholder
		$this->register_placeholder( array(	'slug', 'post_type', 'title' ) );
	}
}