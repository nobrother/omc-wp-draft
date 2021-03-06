<?php
/**
 *===================
 * Helper functions
 *===================
 * load_external_font( $handler = '', $url = '' )
 * load_theme_less( $filename = '', $slug = '' )
 * 
 *
 *===================
 * Script functions
 *===================
 * - omc_load_fontawesome
 * - omc_load_opensans
 * - omc_load_fonthead
 */

/**
 *===================
 * Hook sequence
 *===================
 *-----------------------------
 * wp_enqueue_scripts sequence
 *-----------------------------
 * 1			- omc_enqueue_fonts
 * 10 		- omc_enqueue_js_plugin: load all js plugin, js and css
 * 20 		- omc_enqueue_theme_stylesheet
 * 30			- omc_load_page_stylesheet
 *
 *--------------------
 * wp_head sequence
 *--------------------
 * 1			- wp_enqueue_scripts (head)
 * 7			- omc_before_print_styles
 * 8			- wp_print_styles
 * 8			- omc_embed_css
 * 8			- omc_last_css
 */

/**
 * Load font
 */
add_action( 'wp_enqueue_scripts', 'omc_enqueue_fonts', 1 );
function omc_enqueue_fonts(){
	// Add extra fonts
	do_action( 'omc_enqueue_fonts' );
}

/**
 * Enqueue main style sheet.
 */
add_action( 'wp_enqueue_scripts', 'omc_enqueue_theme_stylesheet', 20 );
function omc_enqueue_theme_stylesheet() {
	if( is_tablet() )
		wp_enqueue_style( 'omc-tablet', omc_theme_css_path_url( 'tablet', 'url' ) );
	else if( is_mobile() )
		wp_enqueue_style( 'omc-mobile', omc_theme_css_path_url( 'mobile', 'url' ) );
	else
		wp_enqueue_style( 'omc-pc', omc_theme_css_path_url( 'pc', 'url' ) );
}

/**
 * Load page specific stylesheet
 */
add_action( 'wp_enqueue_scripts', 'omc_load_page_stylesheet_hook', 30 );
function omc_load_page_stylesheet_hook(){
	do_action( 'omc_load_page_stylesheet' );
}

/**
 * Place omc_before_print_styles hook
 */
add_action( 'wp_head', 'omc_before_print_styles_hook', 7 );
function omc_before_print_styles_hook(){
	do_action( 'omc_before_print_styles' );
}

/**
 * Place omc_embed_css hook
 */
add_action( 'wp_head', 'omc_embed_css_hook', 8 );
function omc_embed_css_hook(){
	do_action( 'omc_embed_css' );
}

/**
 * Place omc_last_css hook
 */
add_action( 'wp_head', 'omc_last_css_hook', 8 );
function omc_last_css_hook(){
	do_action( 'omc_last_css' );
}

/**
 * Enqueue OMC admin styles.
 */
add_action( 'admin_enqueue_scripts', 'omc_load_admin_styles' );
function omc_load_admin_styles() {	
	wp_enqueue_style( 'omc-admin', omc_theme_css_path_url( 'admin', 'url' ) );
}



/**
 * Helper function to get theme css path/url
 */
function omc_theme_css_path_url( $device, $type = 'path' ){
	return ( $type == 'path' ? CACHES_DIR : CACHES_URL ).'/'.$device.'.css';
}
 
 
/**
 * Helper function to load external font
 */
function load_external_font( $handler = '', $url = '' ){

	// Check input
	if( 
		empty( $handler ) ||
		empty( $url ) ||
		!is_url( $url )
	){
		return false;
	}
	
	add_action( 'omc_embed_script', function() use ( $handler, $url ){ ?>
		<script>
			(function(d){
				var head  = document.getElementsByTagName('head')[0];
				var link  = document.createElement('link');
				link.id   = 'font-<?php esc_attr_e( $handler ) ?>';
				link.rel  = 'stylesheet';
				link.type = 'text/css';
				link.href = '<?php echo $url ?>';
				link.media = 'all';
				head.appendChild(link);
			})(document);
		</script>
	<?php } );
	
	return true;
}

/**
 * Helper function to load font head
 */
function omc_load_fonthead(){
	wp_enqueue_style( 'fh', OMC_FONTS_URL.'/font-heads/font-heads.css' );	
}

/**
 * Helper function to load opensan
 */
function omc_load_opensans(){
	wp_enqueue_style( 'opensans', 'http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,300,600,700' );	
}