<?php
/**
 * Create HTML list of Info categories.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Walker_Info_Category extends Walker {
	/**
	 * What the class handles.
	 *
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * Database fields to use.
	 *
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @see wp_list_categories()
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of category. Used for tab indentation.
	 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
	 *                       @wsee wp_list_categories()
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category in reference to parents. Default 0.
	 * @param array  $args     An array of arguments. @see wp_list_categories()
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {
		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = esc_attr( $category->name );

		// Don't generate an element if the category name is empty.
		if ( ! $cat_name ) {
			return;
		}

		$link = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';

		$link .= '>';
		$link .= $cat_name . '</a>';

		if ( ! empty( $args['show_count'] ) ) {
			$link .= ' (' . $category->count . ')';
		}
		
		$output .= "\t<li";
		$css_classes = array(
			'cat-item',
			'cat-item-' . $category->term_id,
		);

		if ( ! empty( $args['current_term_id'] ) ) {
			$_current_category = get_term( $args['current_term_id'], $category->taxonomy );
			if ( $category->term_id == $args['current_term_id'] ) {
				$css_classes[] = 'current-cat';
			} elseif ( $category->term_id == $_current_category->parent ) {
				$css_classes[] = 'current-cat-parent';
			}
		}

		$output .=  ' class="' . implode( ' ', $css_classes ) . '"';
		$output .= ">$link\n";
		
		// List post
		if( !empty( $args['list_posts'] ) ){
			$query = new WP_Query( array(
				'post_type' => 'info',
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'tax_query' => array(
						array(
							'taxonomy' => 'info_ctg',
							'terms'    => $category->term_id,
						),
					),
			) );
			
			if( $query->have_posts() ){
				$output .= '<ul class="cat-item-posts">';
				while( $query->have_posts() ){
					$query->the_post();
					$output .= '<li class="cat-item-post">';
					$output .= 		'<a href="'.get_permalink().'">';
					$output .= 			get_the_title();					
					$output .= 		'</a>';					
					$output .= '</li>';
				}
				$output .= '</ul>';
			}
 
			// Use reset to restore original query.
			wp_reset_postdata();
		}
		
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page   Not used.
	 * @param int    $depth  Depth of category. Not used.
	 * @param array  $args   An array of arguments. Only uses 'list' for whether should append to output. @see wp_list_categories()
	 */
	public function end_el( &$output, $page, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}

}