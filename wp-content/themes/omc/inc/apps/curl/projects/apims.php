<?php 

require_once '/../curl.php';
require_once OMC_APPS_DIR.'/dom/simple_html_dom.php';

class OMC_APIMS_Curl extends OMC_Curl{
	
	public function __construct(){
		
		parent::__construct();
		$this->url = 'http://apims.doe.gov.my/v2/';
	}
	
	public function scrape_index(){
		
		$response = $this->create()->execute()->response;
		
		// DOM extract
		$html = str_get_html( $response );
		$script = $html->find( 'script[!src]', 0 );
		if( !$script ){
			$this->result = false;
			$this->result_error = 'Cannot find relevent <script>';
		}	
		$script = trim( $script->innertext );
		
		// Extract array string
		if( !preg_match( '/values\:\[(.* )\]\,/', $script, $matches ) ){
			$this->result = false;
			$this->result_error = 'Cannot find relevent array';			
		}
		
		// Create array
		$arr = explode( ',  ', trim( $matches[1] ) );
		$result = array();
		foreach( $arr as $value ){
			if( !preg_match( '/latLng:(.*), *data:(.*), *options:(.*)/', $value, $matches ) || count( $matches ) !== 4 )
				continue;
			$tmp = array();
			
			// Scrape latlng
			$tmp['latLng'] = json_decode( $matches[1] );
			
			// Scrape table
			$table = str_get_html( $matches[2] );
			if( $table ){
				$td1 = $table->find( 'td', 0 );
				if( $td1 ){
					$text = $td1->innertext;
					list( , $tmp['raw_api'], $tmp['status'] ) = explode( '<h2>', str_replace( '</h2>', '<h2>', $text ) );
				}
				$td2 = $table->find( 'td', 1 );
				if( $td2 ){
					$text = str_replace( '<br>', '', $td2->innertext );
					list( , $tmp['place'], $tmp['datetime'] ) = explode( '<h2>', str_replace( '</h2>', '<h2>', $text ) );
				}
				
				if( preg_match( '/(\d+)(.*)/', $tmp['raw_api'], $matches ) )
					list( , $tmp['api'], $tmp['indicator'] ) =  $matches;
				$tmp['api'] = (int) $tmp['api'];
				
				$tmp['datetime'] = date( 'Y-m-d H:i:s', strtotime( str_replace( '-', '', $tmp['datetime'] ) ) );
				//$tmp['datetime'] = date( 'l jS \of F Y h:i:s A', $tmp['datetime'] );
			}
			
			$result[] = $tmp;
		}
		
		$this->result = $result;			
		
		return $this;
	}	
}