<?php 

namespace OMC;

class Router{
	protected $urls = array();
	
	function __construct(){
	
		add_action( 'template_include', array( $this, 'template_include' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'generate_rewrite_rules' ) );
		add_action( 'query_vars', array( $this, 'query_vars' ) );
	}
	
	/**
	 * Rewrite the permalinks
	 */
	public function generate_rewrite_rules( $wp_rewrite ) {
		
		$urls = apply_filters( 'omc_custom_urls', $this->urls );
		$rules = array();
		
		foreach( $urls as $key => $value ){
			$value += array(
				'url_id' => $key,
				'omc_custom_page' => 1,
			);
			$rules[$value['url']] = add_query_arg( $value, "index.php" );
		}
		
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}
	
	/**
	 * Loader template
	 */
	function template_include( $template ){
		// check if we have an custom url
		if ( 
			get_query_var( 'omc_custom_page' ) == 1 
			&& ( $template_file = get_query_var( 'template' ) )
			&& file_exists( $template_file )
		){
			return $template_file;
		}
		
		return $template;
	}
	
	/**
	 * query var registration
	 */
	public function query_vars( $qvars ){
		$urls = apply_filters( 'omc_custom_urls', $this->urls );
		$vars = array( 'url_id' => '', 'omc_custom_page' => '' );
		
		foreach( $urls as $value )
			$vars += $value;
			
		return array_merge( $qvars, array_keys( $vars ) );
	}
}

new Router;