<?php

namespace OMC\Page;
use OMC\Post_Object;
use OMC\Post_Object_Ajax;
use \WP_Exception as WP_Exception;
	
/*
 * Ajax
 */
class Ajax extends Post_Object_Ajax {
	
	public $post_type = 'page';
	protected $action_prefix = 'omc_page_';
	
	/*
	 * Construct
	 */
	function __construct(){		

		// Add action hook
		//$this->add_ajax( 'add_video' );
	}
	
}