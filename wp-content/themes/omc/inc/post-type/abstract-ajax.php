<?php

namespace OMC\Post_Type;
use OMC\Abstract_Ajax as Ajax;
use \WP_Exception as e;

/*
 * Post Object Ajax Class
 */
abstract class Abstract_Ajax extends Ajax {
	protected static $post_type;
	protected static $object_classname;
}