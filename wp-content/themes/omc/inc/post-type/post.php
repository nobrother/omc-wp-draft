<?php

namespace OMC\Post;
use OMC\Post_Object_Custom_Settings;
use OMC\Post_Object;
use OMC\Post_Object_Ajax;
use \WP_Exception as WP_Exception;

/*
 * Custom settings
 */
class Custom_Settings extends Post_Object_Custom_Settings {
	public $post_type = 'post';
}

/*
 * Main
 */
class Post extends Post_Object {
	
	// Define variable
	protected $name = 'post';
	const HASH = 'asdasbsifn912g'; 
	public $default_meta = array(
		'numbering' => 0,
		'like_count' => 0,
		'like_user_hash' => '',
		'view_count' => 0,		
	);
	
	/*
	 * Check Is liked
	 */
	function is_liked(){
		if( empty( $this->id ) )
			return new WP_Error( 'post_not_loaded', 'Post is empty' );
			
		if( empty( $_COOKIE['unique_user_id'] ) )
			return new WP_Error( 'unique_user_id_not_set', 'Unknown user' );
		
		return strpos( $this->like_user_hash, '|'.$_COOKIE['unique_user_id'].'|' ) !== false;
		
	}
	
	/*
	 * Plus view count
	 * Use this before header is sent
	 */
	function plus_view_count(){
		
		if( empty( $this->id ) )
			return false;
		
		// Start session if not yet start
		maybe_start_session();
		
		// Had view in this session		
		if( !empty( $_SESSION['post_'.$this->id]['viewed'] ) )
			return false;
		
		// Set session
		$_SESSION['post_'.$this->id]['viewed'] = 1;
		
		global $wpdb;
		
		// Start transaction
		$wpdb->query( 'START TRANSACTION' );
		
		$this->view_count = get_post_meta( $this->id, 'view_count', true );
		update_post_meta( $this->id, 'view_count', ++$this->view_count );
		
		// Commit transaction
		$wpdb->query( 'COMMIT' );
		
		
		return $this->view_count;
	}
	
	/*
	 * Toggle like
	 */
	function toggle_like(){
		$liked = $this->is_liked();
		if( is_wp_error( $liked ) )
			return $liked;
		
		$needle = '|'.$_COOKIE['unique_user_id'].'|';
		
		global $wpdb;
		
		// Start transaction
		$wpdb->query( 'START TRANSACTION' );
		
		// Reload Meta
		$this->like_user_hash = get_post_meta( $this->id, 'like_user_hash', true );
		$this->like_count = get_post_meta( $this->id, 'like_count', true );
		
		// Unlike it
		if( $liked ){
			$this->like_user_hash = str_replace( $needle, '', $this->like_user_hash );
			$this->like_count = max( --$this->like_count, 0 );
		}
		
		// Like it
		else {
			$this->like_user_hash .= $needle;
			++$this->like_count;
		}
		
		// Store
		update_post_meta( $this->id, 'like_user_hash', $this->like_user_hash );
		update_post_meta( $this->id, 'like_count', $this->like_count );
		
		// Commit transaction
		$wpdb->query( 'COMMIT' );
		
		return !$liked;
	}
	
	
	/**
	 * Retrieve a post's terms as a list with specified format.
	 */
	function get_tags_list( $options = array() ) {
		
		if( empty( $this->id ) )
			return false;
		
		// Default options
		$defaults = array(
			'before' => '<ul class="list-tag-cloud"><li>',
			'sep' => '</li><li>',
			'after' => '</li></ul>',
			'attribute' => 'class="btn btn-primary"',
			'with_icon' => false,
		);		
		extract( $options + $defaults );
		
		$terms = get_the_terms( $this->id, 'post_tag' );

		if ( is_wp_error( $terms ) )
			return $terms;

		if ( empty( $terms ) )
			return false;

		$links = array();
		
		foreach ( $terms as $term ) {
			$link = get_term_link( $term, 'post_tag' );
			if ( is_wp_error( $link ) ) {
				return $link;
			}
			
			if( $with_icon )
				$icon = sprintf( '<i class="fa %s"></i>', get_term_meta( $term->term_id, 'icon', true ) );
			else
				$icon = '';
			
			$links[] = $icon.'<a href="' . esc_url( $link ) . '" '.$attribute.'>' . $term->name . '</a>';
		}
		return $before . join( $sep, $links ) . $after;
	}
}