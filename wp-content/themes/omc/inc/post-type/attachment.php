<?php

namespace OMC\Attachment;
use OMC\Post_Object_Custom_Settings;
use OMC\Post_Object;

class Main{

	function __construct(){
	
		add_action( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_filter( 'attachment_link', array( $this, 'link' ), 10, 2 );
	}
	
	/**
	 * Rewrite the permalinks
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		$rules = array(
			'(image|pdf|media)/([0-9]{1,})/?$' => 'index.php?attachment_id=$matches[2]'
		);
		
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}
	function link( $url, $post_id ) {
		if( wp_attachment_is_image( $post_id ) )
			return home_url( '/image/'.$post_id.'/' );
		else{
			switch( get_post_mime_type( $post_id ) ){
				case 'application/pdf':
					return home_url( '/pdf/'.$post_id.'/' );
				
				default:
					return home_url( '/media/'.$post_id.'/' );
			}
		}
	}
}
new Main;

/*
 * Custom settings
 */
class Custom_Settings extends Post_Object_Custom_Settings {
	public $post_type = 'attachment';
}

class Object extends Post_Object{
	// Define variable
	protected $name = 'attachment';
	const HASH = 'asdasbsifn912g'; 
	public $default_meta = array(
		'classes' => 'general',
	);
}