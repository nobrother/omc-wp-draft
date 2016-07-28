<?php

namespace OMC;
use \WP_Exception as e;

/*
 * Post Object Ajax Class
 */
abstract class Abstract_Ajax{
	
	protected static $action_prefix = 'omc_';
	protected static $object_classname;
	
	// Load object from post data
	protected static function get_object( $key = 'post_id' ){
		
		if( !empty( $_POST[$key] ) ){
			$obj = new static::$object_classname( $_POST[$key] );
			return $obj;
		}
		
		throw new e( 'error|no_object', 'Object is not provided' );
	}
	
	// Add Ajax
	protected static function add_ajax( $action, $nopriv = true ){

		if( empty( $action ) )
			return false;
		
		$classname = get_called_class();
		
		if( !method_exists( $classname, $action ) )
			return false;
		
		// Add action hook
		add_action( 'wp_ajax_'.static::$action_prefix.$action, array( $classname, $action ) );

		if( !empty( $nopriv ) )
			add_action( 'wp_ajax_nopriv_'.static::$action_prefix.$action, array( $classname, $action ) );

	}

	/**
	 * Output headers for JSON requests
	 */
	static function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}
	
	/**
	 * Return exception result
	 */
	static function error_result( $e ){
		return array(
			'status' => '0',
			'error' => $e->getMessage(),
		);
	}
	
	/**
	 * Echo result
	 */
	static function return_result( $result = '', $show_POST = false ){
		
		if( is_object( $result ) )
			$result = (array) $result;
		
		if( true === $show_POST )
			$result['postdata'] = $_POST;
		
		if( is_array( $result ) ){
			self::json_headers();
			echo json_encode( $result );
		} else {
			echo $result;
		}
		
		die();
	}
}