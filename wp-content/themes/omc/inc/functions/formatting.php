<?php
/**
 * OMC Formatting functions
 */

/**
 * Return a phrase shortened in length to a maximum number of characters.
 *
 * Result will be truncated at the last white space in the original string. In this function the word separator is a
 * single space. Other white space characters (like newlines and tabs) are ignored.
 *
 * If the first `$max_characters` of the string does not contain a space character, an empty string will be returned.
 *
 * @since 1.4.0
 *
 * @param string $text            A string to be shortened.
 * @param integer $max_characters The maximum number of characters to return.
 *
 * @return string Truncated string
 */
function omc_truncate_phrase( $text, $max_characters ) {

	$text = trim( $text );

	if ( mb_strlen( $text ) > $max_characters ) {
		//* Truncate $text to $max_characters + 1
		$text = mb_substr( $text, 0, $max_characters + 1 );

		//* Truncate to the last space in the truncated string
		$text = trim( mb_substr( $text, 0, mb_strrpos( $text, ' ' ) ) );
	}

	return $text;
}

/**
 * Return content stripped down and limited content.
 *
 * Strips out tags and shortcodes, limits the output to `$max_char` characters, and appends an ellipsis and more link to the end.
 *
 * @since 0.1.0
 *
 * @param integer $max_characters The maximum number of characters to return.
 * @param string  $more_link_text Optional. Text of the more link. Default is "(more...)".
 * @param bool    $stripteaser    Optional. Strip teaser content before the more text. Default is false.
 *
 * @return string Limited content.
 */
function get_the_content_limit( $max_characters, $more_link_text = '(more...)', $stripteaser = false ) {

	$content = get_the_content( '', $stripteaser );

	//* Strip tags and shortcodes so the content truncation count is done correctly
	$content = strip_tags( strip_shortcodes( $content ), apply_filters( 'get_the_content_limit_allowedtags', '<script>,<style>' ) );

	//* Remove inline styles / scripts
	$content = trim( preg_replace( '#<(s(cript|tyle)).*?</\1>#si', '', $content ) );

	//* Truncate $content to $max_char
	$content = omc_truncate_phrase( $content, $max_characters );

	//* More link?
	if ( $more_link_text ) {
		$link   = apply_filters( 'get_the_content_more_link', sprintf( '&#x02026; <a href="%s" class="more-link">%s</a>', get_permalink(), $more_link_text ), $more_link_text );
		$output = sprintf( '<p>%s %s</p>', $content, $link );
	} else {
		$output = sprintf( '<p>%s</p>', $content );
		$link = '';
	}

	return apply_filters( 'get_the_content_limit', $output, $content, $link, $max_characters );

}

/**
 * Echo the limited content.
 *
 * @since 0.1.0
 *
 * @uses get_the_content_limit() Return content stripped down and limited content.
 *
 * @param integer $max_characters The maximum number of characters to return.
 * @param string  $more_link_text Optional. Text of the more link. Default is "(more...)".
 * @param bool    $stripteaser    Optional. Strip teaser content before the more text. Default is false.
 */
function the_content_limit( $max_characters, $more_link_text = '(more...)', $stripteaser = false ) {

	$content = get_the_content_limit( $max_characters, $more_link_text, $stripteaser );
	echo apply_filters( 'the_content_limit', $content );

}

/**
 * Add `rel="nofollow"` attribute and value to links within string passed in.
 *
 * @since 1.0.0
 *
 * @uses omc_strip_attr() Remove any existing rel attribute from links.
 *
 * @param string $text HTML markup.
 *
 * @return string Amendment HTML markup.
 */
function omc_rel_nofollow( $text ) {

	$text = omc_strip_attr( $text, 'a', 'rel' );
	return stripslashes( wp_rel_nofollow( $text ) );

}

/**
 * Remove attributes from a HTML element.
 *
 * This function accepts a string of HTML, parses it for any elements in the `$elements` array, then parses each element
 * for any attributes in the `$attributes` array, and strips the attribute and its value(s).
 *
 * ~~~
 * // Strip class attribute from an anchor
 * omc_strip_attr(
 *     '<a class="my-class" href="http://google.com/">Google</a>',
 *     'a',
 *     'class'
 * );
 * // Strips class and id attributes from div and span elements
 * omc_strip_attr(
 *     '<div class="my-class" id="the-div"><span class="my-class" id="the-span"></span></div>',
 *     array( 'div', 'span' ),
 *     array( 'class', 'id' )
 * );
 * ~~~
 *
 * @since 1.0.0
 *
 * @link http://studiopress.com/support/showthread.php?t=20633
 *
 * @param string       $text       A string of HTML formatted code.
 * @param array|string $elements   Elements that $attributes should be stripped from.
 * @param array|string $attributes Attributes that should be stripped from $elements.
 * @param boolean      $two_passes Whether the function should allow two passes.
 *
 * @return string HTML markup with attributes stripped.
 */
function omc_strip_attr( $text, $elements, $attributes, $two_passes = true ) {

	//* Cache elements pattern
	$elements_pattern = implode( '|', (array) $elements );

	//* Build patterns
	$patterns = array();
	foreach ( (array) $attributes as $attribute ) {
		//* Opening tags
		$patterns[] = sprintf( '~(<(?:%s)[^>]*)\s+%s=[\\\'"][^\\\'"]+[\\\'"]([^>]*[^>]*>)~', $elements_pattern, $attribute );

		//* Self closing tags
		$patterns[] = sprintf( '~(<(?:%s)[^>]*)\s+%s=[\\\'"][^\\\'"]+[\\\'"]([^>]*[^/]+/>)~', $elements_pattern, $attribute );
	}

	//* First pass
	$text = preg_replace( $patterns, '$1$2', $text );

	if ( $two_passes ) //* Second pass
		$text = preg_replace( $patterns, '$1$2', $text );

	return $text;

}

/**
 * Sanitize multiple HTML classes in one pass.
 *
 * Accepts either an array of `$classes`, or a space separated string of classes and sanitizes them using the
 * `sanitize_html_class()` WordPress function.
 *
 * @since 2.0.0
 *
 * @param $classes       array|string Classes to be sanitized.
 * @param $return_format string       Optional. The return format, 'input', 'string', or 'array'. Default is 'input'.
 *
 * @return array|string Sanitized classes.
 */
function omc_sanitize_html_classes( $classes, $return_format = 'input' ) {

	if ( 'input' === $return_format ) {
		$return_format = is_array( $classes ) ? 'array' : 'string';
	}

	$classes = is_array( $classes ) ? $classes : explode( ' ', $classes );

	$sanitized_classes = array_map( 'sanitize_html_class', $classes );

	if ( 'array' === $return_format )
		return $sanitized_classes;
	else
		return implode( ' ', $sanitized_classes );

}

/**
 * Return an array of allowed tags for output formatting.
 *
 * Mainly used by `wp_kses()` for sanitizing output.
 *
 * @since 1.6.0
 *
 * @return array Allowed tags.
 */
function omc_formatting_allowedtags() {

	return apply_filters(
		'omc_formatting_allowedtags',
		array(
			'a'          => array( 'href' => array(), 'title' => array(), ),
			'b'          => array(),
			'blockquote' => array(),
			'br'         => array(),
			'div'        => array( 'align' => array(), 'class' => array(), 'style' => array(), ),
			'em'         => array(),
			'i'          => array(),
			'p'          => array( 'align' => array(), 'class' => array(), 'style' => array(), ),
			'span'       => array( 'align' => array(), 'class' => array(), 'style' => array(), ),
			'strong'     => array(),

			//* <img src="" class="" alt="" title="" width="" height="" />
			//'img'        => array( 'src' => array(), 'class' => array(), 'alt' => array(), 'width' => array(), 'height' => array(), 'style' => array() ),
		)
	);

}

/**
 * Wrapper for `wp_kses()` that can be used as a filter function.
 *
 * @since 1.8.0
 *
 * @uses omc_formatting_allowedtags() List of allowed HTML elements.
 *
 * @param string $string Content to filter through kses.
 *
 * @return string
 */
function omc_formatting_kses( $string ) {

	return wp_kses( $string, omc_formatting_allowedtags() );

}

/**
 * Convert time to human friendly string
 */
function omc_human_time( $old_time, $new_time = false ) {
	
	$old_time = strtotime( $old_time );
	$new_time = strtotime( $new_time );
	if( !$new_time )
		$new_time = current_time( 'timestamp' );
		
	$diff = $new_time - $old_time;
	
	if ( $diff < 10 ){
		return 'Just now';
	}

	$a = array( 365 * 24 * 60 * 60  =>  'year',
							 30 * 24 * 60 * 60  =>  'month',
								7 * 24 * 60 * 60  =>  'week',
										24 * 60 * 60  =>  'day',
												 60 * 60  =>  'hour',
															60  =>  'minute',
															 1  =>  'second'
							);
	$a_plural = array( 'year'   => 'years',
										 'month'  => 'months',
										 'week'  	=> 'weeks',
										 'day'    => 'days',
										 'hour'   => 'hours',
										 'minute' => 'minutes',
										 'second' => 'seconds'
							);

	foreach ( $a as $secs => $str ){
		$d = $diff / $secs;
		switch ( true ){
			case $d >= 1 && $str == 'year': 
				return date( 'D, j M Y \a\t g:ia', $old_time );
			case $d >= 1 && $str == 'month': 
			case $d >= 7 && $str == 'day': 
				return date( 'D, j M \a\t g:ia', $old_time );
			case $d >= 1:
				$r = round($d);
				return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';				
		}
	}

}

/**
 * Calculate day diff
 * This function can exclude saturday, sunday and holiday
 */
function omc_smart_date_diff( $start, $end = '', $is_exclude_first_day = true, $is_exclude_sat = true, $is_exclude_sun = true, $holiday = array() ){
	
	global $timezone;
	
	if( empty( $start ) )
		return;
		
	if( empty( $timezone ) )
		return;
	
	$start = new DateTime( $start, $timezone );
	
	if( !empty( $end ) )
		$end = new DateTime( $end, $timezone );
	else
		$end = new DateTime( 'now', $timezone );
	
	// otherwise the  end date is excluded (bug?)
	if( $is_exclude_first_day === false )
		$end->modify('+1 day');

	$interval = $end->diff($start);
	
	// total days
	$days = $interval->days;
	
	// create an iterateable period of date (P1D equates to 1 day)
	$period = new DatePeriod( $start, new DateInterval('P1D'), $end );
	
	// Deduct nessesory day
	foreach( $period as $dt ) {
	
		// for the updated question
		if ( !empty( $holidays ) && in_array($dt->format('Y-m-d'), $holidays))
			 $days--;

		// substract if Saturday or Sunday
		$curr = $dt->format('D');
		if( $is_exclude_sat && $curr == 'Sat' )
			$days--;
		if( $is_exclude_sun && $curr == 'Sun' )
			$days--;
	}
	
	return $days;
}

/*
 * Converting timestamp excel datetime value
 */
function omc_strtoexceldate( $datetime ){
	return 25569 + strtotime( $datetime ) / 86400 ;
}

/*
 * Date format function
 */
function ddmmmyyyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// 03 Mar 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	return date( 'd M Y', strtotime( $date_str ) );
}
function dmmmyyyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// 3 Mar 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	return date( 'j M Y', strtotime( $date_str ) );
}
function mmmyyyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// Mar 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	if( strlen( $date_str ) == 6 )
		$date_str .= '01';
	return date( 'M Y', strtotime( $date_str ) );
}
function mmmyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// Mar 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	if( strlen( $date_str ) == 6 )
		$date_str .= '01';
	return date( 'M y', strtotime( $date_str ) );
}
function mmmmyyyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// March 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	if( strlen( $date_str ) == 6 )
		$date_str .= '01';
	return date( 'F Y', strtotime( $date_str ) );
}
function ddd_dmmmyyyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// Sun, 3 Mar 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	return date( 'D, d M Y', strtotime( $date_str ) );
}
function dddd_dmmmmyyyy( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// Sunday, 3 March 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	return date( 'l, d F Y', strtotime( $date_str ) );
}

function datetime( $date_str = '0000-00-00 00:00:00', $no_date = '' ){
	// Sunday, 3 March 2013
	if( empty( $date_str ) || $date_str == '0000-00-00' || $date_str == '0000-00-00 00:00:00' ) return $no_date;
	return date( 'j M Y, g:i a', strtotime( $date_str ) );
}

/**
 * Mark up content with code tags.
 *
 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
 *
 * Used almost exclusively within labels and text in user interfaces added by OMC.
 *
 * @since 2.0.0
 *
 * @param  string $content Content to be wrapped in code tags.
 *
 * @return string Content wrapped in code tags.
 */
function omc_code( $content ) {

	return '<code>' . esc_html( $content ) . '</code>';

}
