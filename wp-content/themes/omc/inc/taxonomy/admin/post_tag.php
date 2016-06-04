<?php 
namespace OMC\Taxonomy\Admin;

class Post_Tag extends Taxonomy{
	public $taxonomy = 'post_tag';
	public $default_meta = array(
		'icon' => 'fa-laptop',
	);
}

new Post_Tag();