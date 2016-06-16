<?php

namespace OMC;

/*
 * Post Object Ajax Class
 */
abstract class Post_Object_Ajax{
	
	// Should define in child class
	// public $post_type;
	protected $action_prefix;

	/*
	 * Construct
	 */
	function __construct(){		

		// Add action hook
		//$this->add_ajax( 'add_video' );
	}

	// Add Ajax
	protected function add_ajax( $action, $nopriv = true ){

		if( empty( $action ) )
			return false;

		if( !method_exists( $this, $action ) )
			return false;

		// Add action hook
		add_action( 'wp_ajax_'.$this->action_prefix.$action, array( $this, $action ) );

		if( !empty( $nopriv ) )
			add_action( 'wp_ajax_nopriv_'.$this->action_prefix.$action, array( $this, $action ) );

	}

	/**
	 * Output headers for JSON requests
	 */
	protected function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}
	
	/**
	 * Return exception result
	 */
	protected function error_result( $e ){
		return array(
			'status' => '0',
			'error' => $e->getMessage(),
		);
	}
	
	/**
	 * Echo result
	 */
	protected function return_result( $result = '' ){
		
		if( is_object( $result ) )
			$result = (array) $result;
		
		if( is_array( $result ) ){
			$this->json_headers();
			echo json_encode( $result );
		} else {
			echo $result;
		}
		
		die();
	}
}