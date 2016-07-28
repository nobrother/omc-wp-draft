<?php

namespace OMC\Taxonomy\Portfolio_Tag;
use \WP_Exception as e;

/*
 * Taxonomy
 */ 
class Object {	
	
	// Should declare in child class
	public static $taxonomy = 'portfolio_tag';
	public static $default_meta = array(
		'icon' => 'fa-laptop',
	);
}