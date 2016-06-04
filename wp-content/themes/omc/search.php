<?php
if( !is_mobile() )
	get_template_part( 'templates/search', 'pc' );
else
	get_template_part( 'templates/search', 'mobile' );
