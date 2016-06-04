<?php

add_filter( 'omc_body_attribute', function( $attr ){
	return $attr.' style="background:url(https://s-media-cache-ak0.pinimg.com/236x/ea/72/d7/ea72d7aee39caf7ba37ccc57c60d1e5e.jpg);height: 100vh;"';	
} );

get_header( '404' );



get_footer( '404' );