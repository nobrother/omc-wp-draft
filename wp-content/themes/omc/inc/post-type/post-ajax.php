<?php
namespace OMC\Post;
use OMC\Post_Object_Ajax;

/*
 * Ajax
 */
class Ajax extends Post_Object_Ajax {
	
	public $post_type = 'post';
	protected $action_prefix = 'omc_post_';
	
	/*
	 * Construct
	 */
	function __construct(){
		
		// Load moment if any
		if( !empty( $_POST['pid'] ) ){
			$this->post = new Post( $_POST['pid'] );
			if( empty( $this->post ) )
				$this->post = false;
		}
		else
			$this->post = false;
		
		// Add action hook
		$this->add_ajax( 'toggle_like' );
		$this->add_ajax( 'plus_view_count' );
	}
	
	// Toggle like
	function toggle_like(){
		
		// Security check
		if( 
			$this->post &&
			!empty( $_COOKIE['unique_user_id'] )
		){
			$liked = $this->post->toggle_like();
			if( is_wp_error( $liked ) ){
				$output = array( 'error' => 'Toggle fail' );
			}
			
			else {
				$output = array( 
					'liked' => $liked,
					'like_count' => $this->post->like_count,
				);
			}			
			
		} else {
			$output = array( 'error' => 'Access denied.' );
		}
		
		$this->json_headers();
		echo json_encode( $output );
		
		die();
		
	}
	
}
new Ajax();