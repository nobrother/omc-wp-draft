<?php 
namespace OMC\Taxonomy\Attachment_Category\Admin;
use OMC\Taxonomy\Abstract_Admin;

class Main extends Abstract_Admin{

	protected static $taxonomy = 'attachment_category';
	protected static $object_classname = '\OMC\Taxonomy\Attachment_Category\Object';
	protected static $default_meta;
	
}

// Initialize
Main::init();