<?php 
namespace OMC\Taxonomy\Portfolio_Tag;
use \OMC\Taxonomy\Abstract_Main;

class Main extends Abstract_Main{
	
	protected static $taxonomy = 'portfolio_tag';
	protected static $object_classname = '\OMC\Taxonomy\Portfolio_Tag\Object';
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
		omc_taxonomy( 'portfolio_tag', 'portfolio', 'Portfolio Tag', 'Portfolio Tags' )
			->register();		
	}
}

// Initialize
Main::init();