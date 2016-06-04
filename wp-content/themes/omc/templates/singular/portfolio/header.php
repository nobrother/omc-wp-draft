<?php
/**
 * The header for our page.
 */ 
namespace OMC\Portfolio; 

global $post;
$slug = $post->post_name;
	
// Load extra font here

// Load body attribute
add_filter( 'omc_body_attribute', function() use( $slug ){ return 'id="portfolio-'.$slug.'" class="view-portfolio"'; }, 5 );

// Load general header
omc_inject( 'header', false ); the_post();