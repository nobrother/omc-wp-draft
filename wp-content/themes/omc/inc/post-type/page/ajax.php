<?php

namespace OMC\Post_Type\Page;
use OMC\Post_Type\Abstract_Ajax;
use \WP_Exception as e;
	
/*
 * Ajax
 */
class Ajax extends Abstract_Ajax {
	
	protected static $post_type = 'page';
	protected static $object_classname = 'OMC\Post_Type\Page\Object';
	protected static $action_prefix = 'omc_page_';
	
	/*
	 * Construct
	 */
	static function init(){
		
		// Add action hook
		static::add_ajax( 'create_template_files' );
	}
	
	// Reset template files
	static function create_template_files(){
		try{
			
			// Get object
			$obj = static::get_object();
			
			// Create template files
			$obj->create_template_files();
			
			// Return success status
			static::return_result( array( 'status' => 1 ) );
			
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}	
}

// Initialize
Ajax::init();