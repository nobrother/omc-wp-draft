<?php 

namespace OMC\Post_Type\Post\Admin;
use OMC\Post_Type\Abstract_Admin;
use OMC\Post_Type\Post\Object;
use \WP_Exception as e;

class Main extends Abstract_Admin {
	protected static $post_type = 'post';
	protected static $object_class = '\OMC\Post_Type\Post\Object';
	protected static $default_meta;	
}

// Initialize
Main::init();