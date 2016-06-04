<?php

/*
 * Remove automatic <p>
 */ 
remove_filter( 'the_content', 'wpautop' );
remove_filter( 'the_excerpt', 'wpautop' );

/*
 * Sort post by numbering
 */
add_action( 'pre_get_posts', 'omc_sort_posts' );
function omc_sort_posts( $query ){
	return;
	
	if( 
		( is_home() || is_tag( 'post_tag' ) )
		&& $query->is_main_query()
	){
		$query->set( 'meta_key', 'numbering' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );
	}
}