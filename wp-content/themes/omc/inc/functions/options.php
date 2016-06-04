<?php
/**
 * OMC Options functions
 */

/**
 * Return option from the options table and cache result.
 *
 * Applies `omc_pre_get_option_$key` and `omc_options` filters.
 *
 * Values pulled from the database are cached on each request, so a second request for the same value won't cause a
 * second DB interaction.
 *
 * @since 0.1.3
 *
 * @uses OMC_SETTINGS_FIELD
 *
 * @param string  $key        Option name.
 * @param string  $setting    Optional. Settings field name. Eventually defaults to `OMC_SETTINGS_FIELD` if not
 *                            passed as an argument.
 * @param boolean $use_cache  Optional. Whether to use the omc cache value or not. Default is true.
 *
 * @return mixed The value of this $key in the database.
 */
function omc_get_option( $key, $setting = null, $use_cache = true ) {


	//* The default is set here, so it doesn't have to be repeated in the function arguments for omc_option() too.
	$setting = $setting ? $setting : OMC_SETTINGS_FIELD;

	//* If we need to bypass the cache
	if ( ! $use_cache ) {
		$options = get_option( $setting );

		if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) )
			return '';

		return is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );
	}

	//* Setup caches
	static $settings_cache = array();
	static $options_cache  = array();

	//* Allow child theme to short-circuit this function
	$pre = apply_filters( 'omc_pre_get_option_' . $key, null, $setting );
	if ( null !== $pre )
		return $pre;

	//* Check options cache
	if ( isset( $options_cache[$setting][$key] ) )
		//* Option has been cached
		return $options_cache[$setting][$key];

	//* Check settings cache
	if ( isset( $settings_cache[$setting] ) )
		//* Setting has been cached
		$options = apply_filters( 'omc_options', $settings_cache[$setting], $setting );
	else
		//* Set value and cache setting
		$options = $settings_cache[$setting] = apply_filters( 'omc_options', get_option( $setting ), $setting );

	//* Check for non-existent option
	if ( ! is_array( $options ) || ! array_key_exists( $key, (array) $options ) )
		//* Cache non-existent option
		$options_cache[$setting][$key] = '';
	else
		//* Option has not been previously been cached, so cache now
		$options_cache[$setting][$key] = is_array( $options[$key] ) ? stripslashes_deep( $options[$key] ) : stripslashes( wp_kses_decode_entities( $options[$key] ) );

	return $options_cache[$setting][$key];

}

/**
 * Echo options from the options database.
 *
 * @since 0.1.3
 *
 * @uses omc_get_option() Return option from the options table and cache result.
 *
 * @param string  $key       Option name.
 * @param string  $setting   Optional. Settings field name. Eventually defaults to OMC_SETTINGS_FIELD.
 * @param boolean $use_cache Optional. Whether to use the omc cache value or not. Default is true.
 */
function omc_option( $key, $setting = null, $use_cache = true ) {
	echo omc_get_option( $key, $setting, $use_cache );
}