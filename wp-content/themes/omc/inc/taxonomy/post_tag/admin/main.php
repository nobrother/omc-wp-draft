<?php 
namespace OMC\Taxonomy\Post_Tag\Admin;
use OMC\Taxonomy\Abstract_Admin;

class Main extends Abstract_Admin{

	protected static $taxonomy = 'post_tag';
	protected static $object_classname = '\OMC\Taxonomy\Post_Tag\Object';
	protected static $default_meta = array(
		'icon' => 'fa-laptop',
	);
}

// Initialize
Main::init();