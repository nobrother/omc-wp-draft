<?php

define( 'DOING_API', true );
require_once( '/../../wp-load.php' );

//var_dump($_COOKIE);
//var_dump(OMC_APPS_DIR);

require_once OMC_APPS_DIR.'/curl/projects/apims.php';

$c = new OMC_APIMS_Curl();
$response = $c->scrape_index()->result;

//echo '<pre>'.$response[0].'</pre>';
var_dump($response);
//echo json_encode( array( 'status' => 1, 'message' => 'success' ) );
die();