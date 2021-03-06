<?php 
class OMC_Curl { 
	public $useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36';  // Pretent to be Chrome
	public $url; 
	public $follow_location; 
	public $timeout; 
	public $max_redirects; 
	public $cookie_file_location; 
	public $post; 
	public $post_fields; 
	public $referer; 
	public $response; 
	public $header; 
	public $include_header; 
	public $no_body; 
	public $status; 
	public $binary_transfer; 
	public $authentication; 
	public $auth_name; 
	public $auth_pass;
	protected $_curl;

	public function __construct(){ 
		
		// Defaults
		$this->useragent = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36';  // Pretent to be Chrome
		$this->url = 'https://www.google.com';
		$this->follow_location = true; 
		$this->timeout = 30; 
		$this->max_redirects = 5; 
		$this->cookie_file_location = dirname(__FILE__).'./cookies/'.
																	( isset( $_COOKIE['unique_user_id'] ) ? $_COOKIE['unique_user_id'] : 
																		( isset( $_COOKIE['PHPSESSID'] ) ? $_COOKIE['PHPSESSID'] : 'all' ) ).
																	'.txt'; 
		$this->post = false; 
		$this->post_fields = ''; 
		$this->referer ="https://www.google.com"; 
		$this->response = ''; 
		$this->header = array(
			'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding' => 'gzip, deflate, sdch',
			'Accept-Language' => 'en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4',
			'Cache-Control' => 'max-age=0',
			'Connection' => 'keep-alive',
			'Host' => preg_replace( '/(https|http)\:\/\//', '', $this->url ),
			'Expect' => '',
		); 
		$this->include_header = false;
		$this->no_body = false; 
		$this->status = ''; 
		$this->binary_transfer = false; 
		$this->authentication = 0; 
		$this->auth_name = ''; 
		$this->auth_pass = ''; 
	}

	public function create(){ 
			
		if( !empty( $this->_curl ) )
			return $this;
			
		$this->_curl = curl_init();
		curl_setopt( $this->_curl, CURLOPT_RETURNTRANSFER, true ); 
		curl_setopt( $this->_curl, CURLOPT_FOLLOWLOCATION, $this->follow_location ); 
		curl_setopt( $this->_curl, CURLOPT_COOKIEJAR, $this->cookie_file_location ); 
		curl_setopt( $this->_curl, CURLOPT_COOKIEFILE, $this->cookie_file_location );
		curl_setopt( $this->_curl,CURLOPT_USERAGENT,$this->useragent ); 
		
		return $this;
	}
	
	public function execute(){
		
		// Header
		$header_output = array();
		if( !empty( $this->header ) && is_array( $this->header ) ){
			foreach( $this->header as $key => $value )
				$header_output[] = $key.':'.$value;
		}
		
		// Set options
		curl_setopt( $this->_curl, CURLOPT_URL, $this->url ); 
		curl_setopt( $this->_curl, CURLOPT_HTTPHEADER, $header_output ); 
		curl_setopt( $this->_curl, CURLOPT_TIMEOUT, $this->timeout ); 
		curl_setopt( $this->_curl, CURLOPT_MAXREDIRS, $this->max_redirects ); 
		curl_setopt( $this->_curl, CURLOPT_HEADER, $this->include_header ); 
		curl_setopt( $this->_curl, CURLOPT_NOBODY, $this->no_body ); 
		curl_setopt( $this->_curl, CURLOPT_BINARYTRANSFER, $this->binary_transfer );
		curl_setopt( $this->_curl, CURLOPT_REFERER,$this->referer ); 
		
		if( !empty( $this->auth_name ) ){
			curl_setopt( $this->_curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
			curl_setopt( $this->_curl, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass); 
		}
		
		if( !empty( $this->post_fields ) ){ 
			curl_setopt( $this->_curl, CURLOPT_POST, true ); 
			curl_setopt( $this->_curl, CURLOPT_POST_FIELDS, $this->post_fields ); 
		} else {
			curl_setopt( $this->_curl, CURLOPT_POST, false );				
		} 
		
		$this->response = curl_exec( $this->_curl ); 
		$this->status = curl_getinfo( $this->_curl ); 
		$this->error = curl_error( $this->_curl );
		
		return $this;
	}
	
	public function close(){
		curl_close( $this->_curl ); 
		return $this;
	}
} 
?>