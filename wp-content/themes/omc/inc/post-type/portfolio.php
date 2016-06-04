<?php

namespace OMC\Portfolio;
use OMC\Post_Object_Custom_Settings;
use OMC\Post_Object;
use \WP_Exception as WP_Exception;

/*
 * Custom settings
 */
class Custom_Settings extends Post_Object_Custom_Settings {
	public $post_type = 'portfolio';
}

/*
 * Main
 */
class Portfolio extends Post_Object {
	
	// Define variable
	protected $name = 'portfolio';
	const HASH = 'asdasfafafadge';	
	
	/*
	 * Construct
	 */
	function __construct( $id = 0 ){
		
		parent::__construct( $id );
		
		// Define sample
		$this->samples[OMC_TEMPLATE_DIR.'/singular/portfolio/'.'##slug##'] = array(
			'post-type-layout.php' => 'layout.php', 
			'post-type-content.php' => 'content.php', 
			'post-type-style.less' => 'style.less',
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