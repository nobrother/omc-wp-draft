<?php 

namespace OMC\Post_Type\Portfolio;
use OMC\Post_Type\Abstract_Main;
use OMC\Post_Type\Portfolio\Object;
use \WP_Exception as e;

class Main extends Abstract_Main{
	
	protected static $post_type = 'portfolio';
	protected static $object_class = '\OMC\Post_Type\Portfolio\Object';
	
	static function init(){
		
		// Action
		add_action( 'omc_initiated', array( __CLASS__, 'register_post_type' ) );
	}
	
	// Register post type
	static function register_post_type(){
		omc_cpt( static::$post_type, 'Portfolio', 'Portfolios' )
		->set_args( 'supports', array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt', 'custom-fields' ) )
		->set_args( 'menu_icon', 'dashicons-archive' )
		->register();
	}
}

// Initialize
Main::init();