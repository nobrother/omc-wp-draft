<?php

require_once OMC_APPS_DIR.'/mobile-detect/Mobile_Detect.php';

$detect = new Mobile_Detect(); 
 
if( !is_mobile() )
	get_template_part( 'templates/taxonomy-moment', 'pc' );
else
	get_template_part( 'templates/taxonomy-moment', 'mobile' );