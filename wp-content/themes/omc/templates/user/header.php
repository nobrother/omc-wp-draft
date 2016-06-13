<?php
/**
 * The header for our page.
 */ 
use OMC\User\Main as Main;

// Load scripts
add_action( 'wp_enqueue_scripts', 'omc_load_backbone' );

// Load body attribute
add_filter( 'omc_body_attribute', function(){ return 'id="page-'.Main::current_user_page().'" class="view-page"'; }, 5 );

// Load general header
omc_inject( 'header', false );