<?php

namespace OMC\Post_Type\Portfolio;
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
		$this->samples[OMC_TEMPLATE_DIR.'/singular/portfolio/'.'##slug##'] = array(
			'post-type-layout.php' => 'layout.php', 
			'post-type-content.php' => 'content.php', 
			'post-type-style.less' => 'style.less',
		);
	}
}