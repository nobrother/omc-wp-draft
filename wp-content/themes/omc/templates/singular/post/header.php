<?php

// Load body attribute
add_filter( 'omc_body_attribute', function(){ return 'id="post"'; }, 5 );

// Load general header
omc_inject( 'header', false );