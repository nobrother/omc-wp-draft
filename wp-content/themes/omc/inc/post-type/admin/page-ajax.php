<?php 

namespace OMC\Page\Admin;
use OMC\Page\Page;
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
		
		if( !empty( $_POST['post_id'] ) )
			$this->page = new Page( $_POST['post_id'] );
		else
			$this->page = new Page();
		
		// Add action hook
		$this->add_ajax( 'create_template_files' );
	}
	
	// Reset template files
	function create_template_files(){
		
		// Create template files
		$this->page->create_template_files();
		die();
	}
	
}

// Initialize
new Ajax;