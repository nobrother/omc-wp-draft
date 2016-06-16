<?php
/**
 * The header for our page.
 */ 

global $post, $attachment;

// Load body attribute
add_filter( 'omc_body_attribute', 
					 function() use( $attachment ){ 
						 return 'id="page-attachment'.$attachment->slug.'" class="view-attachment '.$attachment->classes.'"'; 
					 }, 5 
					);

// Load general header
omc_inject( 'header', false );