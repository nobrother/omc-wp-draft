<?php
/**
 * OMC Footer
 */
if( omc_get_option( 'footer_scripts_after_footer' ) )
	add_action( 'omc_after_footer', 'omc_footer_scripts' );
else
	add_action( 'omc_before_footer', 'omc_footer_scripts' );
/**
 * Echo the footer scripts, defined in Theme Settings.
 */
function omc_footer_scripts() {
	echo apply_filters( 'omc_footer_scripts', omc_option( 'footer_scripts' ) );
}
