<?php 

namespace OMC\Portfolio\Admin;
use OMC\Portfolio\Portfolio;
use OMC\Post_Object_Ajax;
use \WP_Exception as WP_Exception;
	
/*
 * Ajax
 */
class Ajax extends Post_Object_Ajax {
	
	public $post_type = 'portfolio';
	protected $action_prefix = 'omc_portfolio_';
	
	/*
	 * Construct
	 */
	function __construct(){		
		if( !empty( $_POST['post_id'] ) )
			$this->portfolio = new Portfolio( $_POST['post_id'] );
		else
			$this->portfolio = new Portfolio();
		
		// Add action hook
		$this->add_ajax( 'create_template_files' );
	}
	
	// Reset template files
	function create_template_files(){
		
		// Create template files
		$this->portfolio->create_template_files();
		die();
	}
	
}

// Initialize
new Ajax;