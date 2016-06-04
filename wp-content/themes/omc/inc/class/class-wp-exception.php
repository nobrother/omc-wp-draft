<?php
/**  * Allows throwing a `WP_Error` object as an an exception.
  */
class WP_Exception extends Exception {
	/**
	 * Error instance.
	 */
	protected $wp_error;
	/**
	 * WordPress exception constructor.
	 * Identical to WP_Error
	 * Will populate WP_Error to this object if first parameter is WP_Error object
	 */
	public function __construct( $code = '', $message = '', $data = '' ) {
		if ( $code instanceof WP_Error )
			$this->wp_error = $code;
		else
			$this->add( $code, $message, $data );					
		if( $this->wp_error instanceof WP_Error ){						$messages = array_map( 				function( $a, $b ){ return $a.': '.$b; }, 				$this->wp_error->get_error_codes(), 				$this->wp_error->get_error_messages() 			);				
			parent::__construct( implode( '<br>', $messages ), null );
		}		
	}
	/**
	 * Get WP_Error object.
	 */
	function get_wp_error() {
		return $this->wp_error;	}	
	/**	 * Retrieve all error codes	 * returns array List of error codes, if available.	 */	function get_error_codes(){		if( empty( $this->wp_error ) )			return false;
		return $this->wp_error->get_error_code();	}
	/**	 * Retrieve first error code available	 * returns string, int or Empty if there is no error codes	 */	function get_error_code(){		if( empty( $this->wp_error ) )			return false;
		return $this->wp_error->get_error_code();	}
	
	/**	 * Retrieve all error messages or error messages matching code.	 * returns an array of error strings on success, 	 * or empty array on failure (if using code parameter)	 */	function get_error_messages( $code = '' ){		if( empty( $this->wp_error ) )			return false;
		return $this->wp_error->get_error_messages( $code );	}

	/**	 * Get single error message. 	 * This will get the first message available for the code. 	 * If no code is given then the first code available will be used.	 * Returns an error string.	 */
	function get_error_message( $code = '' ){		if( empty( $this->wp_error ) )			return false;
		return $this->wp_error->get_error_message( $code );	}
	
	/**	 * Retrieve error data for error code.	 * Returns mixed or null, if no errors.	 */	function get_error_data( $code = '' ){		if( empty( $this->wp_error ) )			return false;			
		return $this->wp_error->get_error_data( $code );	}
	
	/**	 * Append more error messages to list of error messages.	 * No return.	 */	function add( $code = '', $message ='', $data = '' ){
		// Code cannot be empty		if( empty( $code ) || !is_scalar( $code ) )			return false;
		if( $this->wp_error instanceof WP_Error )			$this->wp_error->add( $code, $message, $data );
		// Create WP Error if not exists		else			$this->wp_error = new WP_Error( $code, $message, $data );
		return true;	}
	/**	 * Add data for error code. The error code can only contain one error data.	 * No return.	 */	function add_data( $data ='', $code = '' ){		if( empty( $this->wp_error ) )			return false;
		$this->wp_error->add_data( $data, $code );
		return true;	}
	/**	 * Remove any messages and data associated with an error code.	 * No return.	 */	function remove( $code = '' ){
		if( empty( $this->wp_error ) )			return false;
		$this->wp_error->remove( $code );		return true;	}
}
function is_wp_exception( $exception ){	return $exception instanceof WP_Exception;}