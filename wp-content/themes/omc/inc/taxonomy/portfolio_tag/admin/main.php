<?php 
namespace OMC\Taxonomy\Portfolio_Tag\Admin;
use OMC\Taxonomy\Abstract_Admin;

class Main extends Abstract_Admin{

	protected static $taxonomy = 'portfolio_tag';
	protected static $object_classname = '\OMC\Taxonomy\Portfolio_Tag\Object';
	protected static $default_meta;
	
}

// Initialize
Main::init();