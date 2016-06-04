<?php

ignore_user_abort(true);

define( 'DOING_API', true );
require_once( '../../wp-load.php' );

// Compile less
compile_less();

echo json_encode( array( 'status' => 1, 'message' => 'success' ) );
die();