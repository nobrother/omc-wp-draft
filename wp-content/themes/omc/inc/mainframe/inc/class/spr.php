<?php
 
class SPR_Hacking {
	
	/**
	 * @var requested header
	 */
	protected $header;
	
	/**
	 * @var curl resource
	 */
	protected $ch;
	
	/**
	 * Result
	 */
	protected $result = array();
	
	/**
	 * Error
	 */
	protected $error = array();
	
	/**
	 * Is initiated
	 */
	protected $is_init = false;
	
	/**
	 * @var The single instance of the class
	 * @since 1.0
	 */
	protected static $_instance = null;
	
	/**
	 * Main Instance
	 *
	 * Ensures only one instance is loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Main instance Class
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Constructor
	 * Placeholder
	 */
	public function __construct() {}
	
	/**
	 * Initial
	 */
	public function init(){
		
		// Prevent double init
		if( $this->is_init )
			return $this;
		
		$user_hash = $_COOKIE['unique_user_id'];
		
		// Load simple dom
		require_once ( OMC_APPS_DIR . '/dom/simple_html_dom.php' );
		
		// Create variable
		$this->header = array(
			'Host: daftarj.spr.gov.my',
			'Connection: keep-alive',
			'Cache-Control: max-age=0',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36',
			'Upgrade-Insecure-Requests: 1',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding: gzip, deflate, sdch',
			'Accept-Language: en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4',
		);
		
		// Preset CURL
		$this->ch = curl_init();		
		curl_setopt( $this->ch, CURLOPT_COOKIEJAR, MAIN_COOKIE_PATH . "/{$user_hash}.txt" );
		curl_setopt( $this->ch, CURLOPT_COOKIEFILE, MAIN_COOKIE_PATH . "/{$user_hash}.txt" );
		curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $this->header );
		curl_setopt( $this->ch, CURLINFO_HEADER_OUT, true );
		curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->ch, CURLOPT_TIMEOUT, 120 );
		
		return $this;
	}
	
	/**
	 * Close
	 */
	public function close(){
		
		if( $this->is_init )
			curl_close( $this->ch );
			
		$this->is_init = false;
		$this->header = null;
		
		return $this;
	}
	
	
	/**
	 * Reset error and result
	 */
	protected function reset(){
		$this->error = null;
		$this->result = array();
	}
	
	/**
	 * Get result
	 */
	public function get_result( $type = '' ){
		return empty( $this->result[$type] ) ? array() : $this->result[$type];
	}
	
	/**
	 * Get error
	 */
	public function get_error( $type = '' ){
		return empty( $this->error[$type] ) ? array() : $this->error[$type];
	}
	
	/**
	 * Task: Single IC check
	 */
	public function single_check( $ic = '' ){
		
		// Reset
		$this->reset();
		
		try {
		
			// Get session key
			curl_setopt( $this->ch, CURLOPT_COOKIESESSION, true );	// Force session to be reset
			curl_setopt( $this->ch, CURLOPT_URL, 'http://daftarj.spr.gov.my/DAFTARJ/DaftarjBM.aspx' );
			
			// Request
			$answer = $this->request();
			
			// Restore the session setting
			curl_setopt( $this->ch, CURLOPT_COOKIESESSION, false );
			curl_setopt( $this->ch, CURLOPT_POST, true );
			
			// Search key
			$html = str_get_html( $answer );
			
			// __VIEWSTATE
			if( empty( $__VIEWSTATE = $html->find( '#__VIEWSTATE', 0 ) ) )
				$__VIEWSTATE = '';
			else
				$__VIEWSTATE = $__VIEWSTATE->value;
			
			// __VIEWSTATEGENERATOR
			if( empty( $__VIEWSTATEGENERATOR = $html->find( '#__VIEWSTATEGENERATOR', 0 ) ) )
				$__VIEWSTATEGENERATOR = '';
			else
				$__VIEWSTATEGENERATOR = $__VIEWSTATEGENERATOR->value;
				
			// __EVENTVALIDATION
			if( empty( $__EVENTVALIDATION = $html->find( '#__EVENTVALIDATION', 0 ) ) )
				$__EVENTVALIDATION = '';
			else
				$__EVENTVALIDATION = $__EVENTVALIDATION->value;
			
			// Check validation key
			if( 
				empty( $__VIEWSTATE ) ||
				empty( $__VIEWSTATEGENERATOR ) ||
				empty( $__EVENTVALIDATION )
			){
				throw new WP_Exception( 'validation_key_not_found', 'Cannot found validation key.' );
			}
			
			$q = array(
				'__EVENTTARGET' => '',
				'__EVENTARGUMENT' => '',
				'__VIEWSTATE' => $__VIEWSTATE,
				'__VIEWSTATEGENERATOR' => $__VIEWSTATEGENERATOR,
				'__EVENTVALIDATION' => $__EVENTVALIDATION,
				'txtIC' => $ic,
				'Semak' => 'Semak',
			);
			
			// Build Query
			curl_setopt( $this->ch, CURLOPT_POSTFIELDS, array_to_query( $q ) );
			
			// Request
			$answer = $this->request();
			
			// Request finish
			$this->close();
			
			// Search table
			$html = str_get_html( $answer );
			$table = $html->find( '#divviewinfo table', 1 );
			
			if( empty( $table ) )
				throw new WP_Exception( 'table_not_found', 'Cannot found voter info table.' );
			
			// Fields
			$fields = array(
				array(
					'key' => 'ic',
					'selector' => '#LabelIC',
					'value' => '',
				),
				array(
					'key' => 'ic_lama',
					'selector' => '#LabelIClama',
					'value' => '',
				),
				array(
					'key' => 'name',
					'selector' => '#Labelnama',
					'value' => '',
				),
				array(
					'key' => 'dob',
					'selector' => '#LabelTlahir',
					'value' => '',
				),
				array(
					'key' => 'gender',
					'selector' => '#Labeljantina',
					'value' => '',
				),
				array(
					'key' => 'lokaliti',
					'selector' => '#Labellokaliti',
					'value' => '',
				),
				array(
					'key' => 'daerah_mengundi',
					'selector' => '#Labeldm',
					'value' => '',
				),
				array(
					'key' => 'dun',
					'selector' => '#Labeldun',
					'value' => '',
				),
				array(
					'key' => 'parlimen',
					'selector' => '#Labelpar',
					'value' => '',
				),
				array(
					'key' => 'negeri',
					'selector' => '#Labelnegeri',
					'value' => '',
				),
				array(
					'key' => 'sign1',
					'selector' => '#Labelsign1',
					'value' => '',
				),
				array(
					'key' => 'label12',
					'selector' => '#Label12',
					'value' => '',
				),
				array(
					'key' => 'status',
					'selector' => '#LABELSTATUSDPI',
					'value' => '',
				),
			);
			// Loop
			foreach( $fields as & $field ){			
				if( !empty( $el = $table->find( $field['selector'], 0 ) ) )
					$field['value'] = trim( $el->plaintext );
			}
			
			return ( $this->result['single_check'] = wp_list_pluck( $fields, 'value', 'key' ) );
			
		} 
		
		// Catch error
		catch( WP_Exception $e ) {
		
			$this->close();
			return $e;
			
		}
	}
	
	/*
	 * Request
	 */
	protected function request(){
		
		if( false === ( $answer = curl_exec( $this->ch ) ) )
			throw new WP_Exception( 'request_curl_error', curl_error( $this->ch ) );
		
		if( empty( $answer ) )
			throw new WP_Exception( 'empty_response', 'The response content is empty.' );
		
		return $answer;
		
	}
	
}	
	
/**
 * Returns the main instance.
 *
 * @since  1.0
 * @return main instance
 */
function spr() {
	return SPR_Hacking::instance();
}

/**
 * Excecute everything
 */
add_action( 'init', 'spr', 11 );