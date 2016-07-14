<?php

ignore_user_abort(true);

define( 'DOING_API', true );
require_once( '../../wp-load.php' );
$t1 = microtime(true);

// Compile less
compile_less();

echo json_encode( array( 
	'status' => 1, 
	'message' => 'success',
	'duration' => microtime(true) - $t1,
	'memory' => memory_get_peak_usage(true)/1024/1024,
) );
die();