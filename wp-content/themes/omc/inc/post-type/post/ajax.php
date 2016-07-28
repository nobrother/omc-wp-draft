<?php

namespace OMC\Post_Type\Post;
use OMC\Post_Type\Abstract_Ajax;
use \WP_Exception as e;
	
/*
 * Ajax
 */
class Ajax extends Abstract_Ajax {
	
	protected static $post_type = 'post';
	protected static $object_classname = 'OMC\Post_Type\Post\Object';
	protected static $action_prefix = 'omc_post_';
	
	/*
	 * Construct
	 */
	static function init(){
		// Add action hook
		static::add_ajax( 'toggle_like' );
	}
	
	// Toggle like
	function toggle_like(){
		
		try{
			// Get object
			$obj = static::get_object();
			
			// Security check
			if( !empty( $_COOKIE['unique_user_id'] ) ){
				$liked = $obj->toggle_like();
				if( is_wp_error( $liked ) ){
					$output = array( 'error' => 'Toggle fail' );
				}
				
				else {
					$output = array( 
						'status' => 1,
						'liked' => $liked,
						'like_count' => $obj->like_count,
					);
				}			
				
			} else {
				$output = array( 'status' => 0, 'error' => 'Access denied.' );
			}
			
			// Return success status
			static::return_result( $output, true );
			
		} catch( e $e ) {
			static::return_result( static::error_result( $e ), true );			
		} catch( \Exception $e ){
			static::return_result( static::error_result( $e ) );
		}
	}
}

// Initialize
Ajax::init();