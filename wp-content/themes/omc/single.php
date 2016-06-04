<?php
if( !is_mobile() )
	get_template_part( 'templates/single', 'pc' );
else
	get_template_part( 'templates/single', 'mobile' );