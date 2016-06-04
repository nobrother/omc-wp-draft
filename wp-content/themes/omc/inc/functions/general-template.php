<?php
/**
 * General template tags that can go anywhere in a template.
 */

/**
 * Make full path to relative to theme
 */
function omc_relative_path( $path, $root = PARENT_DIR ) {
	return str_replace( $root, '', $path );
} 

/*
 * Helper function to add device suffix
 */
function omc_add_device_suffix( $templates, $ext = '' ){
	
	$tmp = array();
	
	if( $ext )
		$ext = '.'.$ext;
	
	foreach( (array) $templates as $template ){		
		if( is_tablet() )
			$tmp[] = "{$template}-tablet{$ext}";
		if( is_phone() )
			$tmp[] = "{$template}-phone{$ext}";
		if( is_mobile() )
			$tmp[] = "{$template}-mobile{$ext}";
		else
			$tmp[] = "{$template}-pc{$ext}";
		$tmp[] = "{$template}{$ext}";
	}
	
	return $tmp;
}


/*
 * Match and replace constants
 */
function omc_replace_constants( $string = '' ){
	
	return preg_replace_callback(
		'/\[\[([^\[]+)\]\]/',
		function( $matches ){
			if( defined( $matches[1] ) )
				return constant( $matches[1] );
			else
				return $matches[0];
		},
		$string
	);
	
}

/*
 * Get most relevent file with omc style
 * Consider the device suffix
 */
function omc_include_file( $file, $slug = "", $data = array() ){

	if( !is_array( $file ) )
		$file = omc_pathinfo( $file );
	
	extract( $file );
	
	$files = array();
	if( !empty( $slug ) )	
		$files = omc_add_device_suffix( "$dirname/$filename-$slug", $extension );
	$files = array_merge( $files, omc_add_device_suffix( "$dirname/$filename", $extension ) );
	
	$file = locate_file( $files );
	
	if( $file ){
		if( $data && is_array( $data ) )
			extract( $data );
		include $file['file'];
	}
}

/*
 * Inject common component in OMC_COMMON_DIR
 */
function omc_inject( $name, $with_device_suffix = true, $data = array() ){
	if( $with_device_suffix )
		omc_include_file( OMC_COMMON_DIR.'/'.$name.'/view.php', '', $data );
	
	else{
		if( $data && is_array( $data ) )
			extract( $data );
		include OMC_COMMON_DIR.'/'.$name.'/view.php';
	}
}

/*
 * Choose singular and prural
 */
function _s( $number = 0, $singular, $prural = '', $zero = '' ){
	if( empty( $prural ) )
		$prural = $singular;
	if( empty( $zero ) )
		$zero = "No $singular";
	
	$number = (double) $number;
	
	if( $number == 0 )
		echo $zero;
	elseif( $number == 1 )
		echo "$number $singular";
	else
		echo "$number $prural";
	
}
 
/*
 * Choose template to loader
 * Apply to filter '{$type}_template'
 * Possible values for `$type` include: 'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date',
 * 'home', 'front_page', 'page', 'paged', 'search', 'single', 'singular', and 'attachment'.
 */
function omc_template_loader( $template ){
	list( $filter,  ) = explode( '_', current_filter() );
	switch( $filter ){
		case 'home': 
			return OMC_TEMPLATE_DIR."/blog/index.php"; break;
		case 'comments': 
			return OMC_COMMON_DIR."/comments/view.php"; break;	
		case 'page': 		
		case 'category': 
		case 'tag': 
		case 'taxonomy': 
		case 'search': 
		case '404': 
			return OMC_TEMPLATE_DIR."/$filter/index.php"; break;		
		case 'single':
		case 'singular':
			return OMC_TEMPLATE_DIR."/singular/index.php"; break;
	}
	return $template;
}

/**
 * Post class
 */
function omc_post_class( $classes, $class, $post_id ){
	if( is_sticky() && is_home() && ! is_paged() )
		array_push( $classes, 'sticky-post', 'featured-post' );
	return $classes;
}
add_filter( 'post_class', 'omc_post_class', 10, 3 );

/**
 * Displays excerpt
 */
function omc_post_excerpt( $class = 'post-excerpt' ) {	 
	global $post;
?>
		<div class="<?php esc_attr_e( $class ); ?>">
			<?php 
				echo $excerpt = get_the_excerpt();
				if( strpos( $excerpt, omc_excerpt_more() ) === false )					
					echo omc_excerpt_more();
			?>
		</div>
<?php }
/**
 * Add 'See more' link
 */
function omc_excerpt_more() {
	$link = sprintf( '<a href="%1$s" class="more-link">%2$s</a>',
		esc_url( get_permalink() ),
		'See more'
	);
	return ' &hellip; ' . $link;
}
function omc_excerpt_add_more( $text, $raw_excerpt ){
	if( $raw_excerpt !== '' ){
		return $text.omc_excerpt_more();
	}
	return $text;
}
add_filter( 'excerpt_more', 'omc_excerpt_more' );
add_filter( 'the_content_more_link', 'omc_excerpt_more' );
add_filter( 'wp_trim_excerpt', 'omc_excerpt_add_more', 10, 2 );

/*
 * Remove img caption inline style
 */
function omc_img_caption_shortcode_width( $width ){
	//return 0;
	return $width;
}
add_filter( 'img_caption_shortcode_width', 'omc_img_caption_shortcode_width' );

/**
 * Allow links to have a target attribute.
 */
function omc_wp_kses_allowed_html( $tags, $context ) {
	$tags['a']['target'] = true;
	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'omc_wp_kses_allowed_html', 10, 2 );

/**
 * Add target _blank to edit post link
 */
function omc_edit_post_link( $link ){
	$pos = strpos( $link, '<a' );
	if( $pos === 0 )
		return substr_replace( $link, ' target="_blank"', $pos + 2, 0 );
	return $link;
}
add_filter( 'edit_post_link', 'omc_edit_post_link' );
/**
 * Prints HTML with meta information for the categories, tags.
 */
function omc_entry_meta() {
	if ( 'post' === get_post_type() ) {
		$author_avatar_size = apply_filters( 'omc_author_avatar_size', 49 );
		printf( '<span class="byline"><span class="author vcard">%1$s<span class="screen-reader-text">%2$s </span> <a class="url fn n" href="%3$s">%4$s</a></span></span>',
			get_avatar( get_the_author_meta( 'user_email' ), $author_avatar_size ),
			_x( 'Author', 'Used before post author name.', 'omc' ),
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			get_the_author()
		);
	}

	if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
		omc_entry_date();
	}

	$format = get_post_format();
	if ( current_theme_supports( 'post-formats', $format ) ) {
		printf( '<span class="entry-format">%1$s<a href="%2$s">%3$s</a></span>',
			sprintf( '<span class="screen-reader-text">%s </span>', _x( 'Format', 'Used before post format.', 'omc' ) ),
			esc_url( get_post_format_link( $format ) ),
			get_post_format_string( $format )
		);
	}

	if ( 'post' === get_post_type() ) {
		omc_entry_taxonomies();
	}

	if ( ! is_singular() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
		echo '<span class="comments-link">';
		comments_popup_link( sprintf( __( 'Leave a comment<span class="screen-reader-text"> on %s</span>', 'omc' ), get_the_title() ) );
		echo '</span>';
	}
}

/**
 * Prints HTML with category and tags for current post.
 */
function omc_entry_taxonomies() {
	$categories_list = get_the_category_list( ', ' );
	if ( $categories_list && omc_categorized_blog() ) 
		printf( '<span class="cat-links"></span>%s</span>',	$categories_list );

	$tags_list = get_the_tag_list( '', ', ' );
	if ( $tags_list )
		printf( '<span class="tags-links">%s</span>',	$tags_list );
}

/**
 * Determines whether blog/site has more than one category.
 *
 * Create your own omc_categorized_blog() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @return bool True if there is more than one category, false otherwise.
 */
function omc_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'omc_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'omc_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so omc_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so omc_categorized_blog should return false.
		return false;
	}
}

/**
 * Flushes out the transients used in omc_categorized_blog().
 *
 * @since Twenty Sixteen 1.0
 */
function omc_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'omc_categories' );
}
add_action( 'edit_category', 'omc_category_transient_flusher' );
add_action( 'save_post',     'omc_category_transient_flusher' );