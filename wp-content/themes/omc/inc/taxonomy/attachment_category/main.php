<?php 
namespace OMC\Taxonomy\Attachment_Category;
use \OMC\Taxonomy\Abstract_Main;

class Main extends Abstract_Main{
	
	protected static $taxonomy = 'attachment_category';
	protected static $object_classname = '\OMC\Taxonomy\Attachment_Category\Object';
	protected static $default_meta;
	
	/*
	 * Initialize
	 */
	static function init(){
		parent::init();		
		
		// Hook
		add_action( 'omc_initiated', array( __CLASS__, 'register_taxonomy' ) );
	}
	
	static function register_taxonomy(){
		
		omc_taxonomy( 'attachment_category', 'attachment', 'Category', 'Categories' )
			->set_like_category()
			->register();
		
	}
}

// Initialize
Main::init();