<?php

class OMC_Shortcodes {
	
	public $shortcodes = array(
		'content' => 'content_func',
	);
	
	// Include
	public function content_func( $atts, $content = "" ){
		if( empty( $content ) )
			return '';
		ob_start();
		get_template_part( 'templates/content', $content );
		return ob_get_clean();
	}
	
	/**
	 * Register shortcode
	 */
	public function register(){
		
		// Register shortcode in init hook
		$this->add_shortcode();
		
		unset( $this->shortcodes );
		
		return $this;
	}
	
	/**
	 * Add shortcode
	 */
	function add_shortcode(){
		if( !empty( $this->shortcodes ) ){
			foreach( $this->shortcodes as $key => $value ){
				if( method_exists( $this, $value ) )
					add_shortcode( $key, array( $this, $value ) );
			}
		}
	}
	
}

/**
 * Function to manage omc shortcode
 * Please use this function instead of direct access
 */
function omc_shortcodes(){
	
	global $omc_shortcodes;
	
	// Create object if never created before
	if( !isset( $omc_shortcodes ) )
		$omc_shortcodes = new OMC_Shortcodes();
		
	return $omc_shortcodes;
}

// Initiate
omc_shortcodes()->register();