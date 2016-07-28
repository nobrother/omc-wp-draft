<?php 

namespace OMC\Post_Type\Post;
use OMC\Post_Type\Abstract_Main;
use \WP_Exception as e;

class Main extends Abstract_Main{
	
	protected static $post_type = 'post';
	protected static $object_class = '\OMC\Post_Type\Post\Object';
	
	static function init(){
		
		// Hooks
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_excerpt', 'wpautop' );
		add_action( 'pre_get_posts', array( __CLASS__, 'sort_posts' ) );
	}
	
	// Set default sorting on post list
	static function sort_posts( $query ){
		return;

		if( 
			( is_home() || is_tag( 'post_tag' ) )
			&& $query->is_main_query()
		){
			$query->set( 'meta_key', 'numbering' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
		}
	}
}

// Initialize
Main::init();