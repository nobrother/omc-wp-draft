<?php
/**
 * Filter the taxonomy used in the Tag Cloud widget.
 */
add_filter( 'widget_tag_cloud_args', 'omc_widget_tag_cloud_args' );
function omc_widget_tag_cloud_args( $args ){
	return array(
		'orderby' => 'count',		// Most popular tag at the front
		'order' => 'DESC',
		'unit' => '',
		'smallest' => 0,
		'largest' => 0,
		'filter' => false,
		'number' => 15,					// Restrict maximum 15 tags
	) + $args;
}

/**
 * Filter the data used to generate the tag cloud.
 * - Remove font-size feature
 */
add_filter( 'wp_generate_tag_cloud_data', 'omc_wp_generate_tag_cloud_data' );
function omc_wp_generate_tag_cloud_data( $tags_data ){
	foreach( $tags_data as & $data )
		$data['font_size'] = 'inherit';	
	return $tags_data;
}
