<?php
/**
 * OMC Header Hook
 */

/**
 * Remove unnecessary code that WordPress puts in the `head`.
 *
 * @since 1.3.0
 *
 * @uses omc_get_option() Get theme setting value
 * @uses omc_get_seo_option() Get SEO setting value
 */
add_action( 'get_header', 'omc_doc_head_control' );
function omc_doc_head_control() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );

}

add_action( 'wp_head', 'omc_load_favicon' );
/**
 * Echo favicon link if one is found.
 *
 * Falls back to OMC theme favicon.
 *
 * URL to favicon is filtered via `omc_favicon_url` before being echoed.
 *
 * @since 0.2.2
 *
 * @uses CHILD_DIR
 * @uses CHILD_URL
 * @uses PARENT_URL
 */
function omc_load_favicon() {

	//* Allow child theme to short-circuit this function
	$pre = apply_filters( 'omc_pre_load_favicon', false );
	
	$favicon = '';
	
	if ( $pre !== false )
		$favicon = $pre;
	elseif ( file_exists( CHILD_DIR . '/favicon.ico' ) )
		$favicon = CHILD_URL . '/favicon.ico';

	$favicon = apply_filters( 'omc_favicon_url', $favicon );

	if ( !empty( $favicon ) )
		echo '<link rel="Shortcut Icon" href="' . esc_url( $favicon ) . '" type="image/x-icon" />' . "\n";

}

add_action( 'wp_head', 'omc_canonical', 5 );
/**
 * Echo custom canonical link tag.
 *
 * Remove the default WordPress canonical tag, and use our custom
 * one. Gives us more flexibility and effectiveness.
 *
 * @since 0.1.3
 *
 * @uses omc_get_seo_option()   Get SEO setting value.
 * @uses omc_get_custom_field() Get custom field value.
 *
 * @global WP_Query $wp_query Query object.
 *
 * @return null Return null on failure to determine queried object.
 */
function omc_canonical() {

	//* Remove the WordPress canonical
	remove_action( 'wp_head', 'rel_canonical' );

	global $wp_query;

	$canonical = '';

	if ( is_front_page() )
		$canonical = trailingslashit( home_url() );

	if ( is_singular() ) {
		if ( ! $id = $wp_query->get_queried_object_id() )
			return;

		//$cf = omc_get_custom_field( '_omc_canonical_uri' );

		//$canonical = $cf ? $cf : get_permalink( $id );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		if ( ! $id = $wp_query->get_queried_object_id() )
			return;

		$taxonomy = $wp_query->queried_object->taxonomy;

		//$canonical = omc_get_seo_option( 'canonical_archives' ) ? get_term_link( (int) $id, $taxonomy ) : 0;
	}

	if ( is_author() ) {
		if ( ! $id = $wp_query->get_queried_object_id() )
			return;

		//$canonical = omc_get_seo_option( 'canonical_archives' ) ? get_author_posts_url( $id ) : 0;
	}

	if ( $canonical )
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( apply_filters( 'omc_canonical', $canonical ) ) );

}

/**
 * Echo header scripts in to wp_head().
 */
if( omc_get_option( 'header_scripts_after_head' ) )
	add_action( 'omc_after_head', 'omc_header_scripts' );
else
	add_action( 'omc_before_head', 'omc_header_scripts' );
function omc_header_scripts() {
	echo apply_filters( 'omc_header_scripts', omc_get_option( 'header_scripts' ) );
}