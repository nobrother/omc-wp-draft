<?php

namespace OMC\Post_Type\Page;
use OMC\Post_Type\Abstract_Object;
use \WP_Exception as WP_Exception;

/*
 * Main
 */
class Object extends Abstract_Object {	
	
	public $placeholder = array( 'slug', 'title', 'post_type', 'id' );
	
	/*
	 * Construct
	 */
	function __construct( $id = 0 ){
		
		parent::__construct( $id );
		
		// Define sample
		$this->samples[OMC_TEMPLATE_DIR.'/page/'.'##slug##'] = array(
			'page-layout.php' => 'layout.php', 
			'page-content.php' => 'content.php', 
			'page-style.less' => 'style.less',
		);
	}
}