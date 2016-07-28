<?php
/**
 *===================
 * Helper functions
 *===================
 * load_js_plugin( $fn = '' ) ==> $fn = Script functions, see below
 * load_theme_js( $js = '', $dependancy = 'main' ) ==> $js = Script filename in JS folder
 * 
 *
 *===================
 * Script functions
 *===================
 * - omc_load_froala_editor
 * - omc_load_codemirror
 * - omc_load_bootstrap
 * - omc_load_dropzone
 * - omc_load_bootstrap_datetimepicker
 * - omc_load_jquery_chosen
 * - omc_load_select2
 * - omc_load_jquery_datatables
 * - omc_load_jquery_sortable
 * - omc_load_jquery_mousewheel
 * - omc_load_jquery_visible
 * - omc_load_jquery_perfect_scroll
 * - omc_load_jquery_autosize
 * - omc_load_jquery_serializeobject
 * - omc_load_bootstrap_validator
 * - omc_load_jquery_countdown
 */

/**
 *===================
 * Hook sequence
 *===================
 *-----------------------------
 * wp_enqueue_scripts sequence
 *-----------------------------
 * 5			- omc_enqueue_jquery
 * 10 		- omc_enqueue_js_plugin: load all js plugin, js and css
 * 20 		- omc_enqueue_theme_scripts
 *
 *--------------------
 * wp_footer sequence
 *--------------------
 * 10			- omc_before_enqueue_scripts
 * 20			- wp_enqueue_scripts (footer)
 * 40			- omc_embed_scripts
 * 50			- omc_last_scripts
 */

/**
 * Load js plugin
 */
add_action( 'wp_enqueue_scripts', 'omc_enqueue_js_plugin', 10 );
function omc_enqueue_js_plugin() {
	//omc_load_bootstrap();
	wp_enqueue_media();
} 
 
/**
 * Helper function to load backbone
 */ 
function omc_load_backbone(){
	wp_enqueue_script( 'underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js', array(), false, true );	
	wp_enqueue_script( 'backbone', 'https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.3.3/backbone-min.js', array(), false, true );	
	//wp_enqueue_script( 'backbone-validation', 'https://cdnjs.cloudflare.com/ajax/libs/backbone.validation/0.11.5/backbone-validation.js', array(), false, true );
	//wp_enqueue_script( 'backbone-validation', OMC_JS_PLUGIN_URL.'/backbone-validation/backbone-validation.js', array(), false, true );
	//wp_enqueue_script( 'backbone-async-validation', OMC_JS_PLUGIN_URL.'/async.backbone.validation/async.backbone.validation.js', array(), false, true );
	wp_enqueue_script( 'backbone-validation-async', OMC_JS_PLUGIN_URL.'/backbone-validation-async/backbone-validation-async.js', array(), false, true );
}


/**
 * Helper function to load froala editor
 */
function omc_load_froala_editor(){
	$js = array(
		'fe-main' => 'js/froala_editor.min',
		'fe-tables' => 'js/plugins/tables.min',
		'fe-urls' => 'js/plugins/urls.min',
		'fe-lists' => 'js/plugins/lists.min',
		'fe-colors' => 'js/plugins/colors.min',
		'fe-font-family' => 'js/plugins/font_family.min',
		'fe-font-size' => 'js/plugins/font_size.min',
		'fe-block-style' => 'js/plugins/block_styles.min',
		'fe-media-manager' => 'js/plugins/media_manager.min',
		'fe-video' => 'js/plugins/video.min',
		'fe-char-counter' => 'js/plugins/char_counter.min',
		'fe-entities' => 'js/plugins/entities.min',
	);	
	$js= array( 'fe-main' => 'froala', );
	
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'froala-editor', $value );
	}
	
	// CSS
	$css = array(
		'fe-editor' => 'css/froala_editor.min',
		'fe-style' => 'css/froala_style.min',
	);
	$css= array( 'fe-style' => 'froala', );
	
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'froala-editor', $value );
	}
	
}

/**
 * Helper function to load code mirror
 */
function omc_load_codemirror(){
	
	global $_omc_codemirror_theme_options;
	$_omc_codemirror_theme_options = array(
		'default',
		'monokai',
		'ambiance',
		'dracula',
		'mbo',
		'neo',
		'pastel-on-dark',
		'yeti',
	);
	
	$js = array(
		// Main
		'cm-main' => 'lib/codemirror',
		
		// Mode
		'cm-xml' => 'mode/xml',
		'cm-javascript' => 'mode/javascript',
		'cm-css' => 'mode/css',
		'cm-htmlmixed' => 'mode/htmlmixed',
		'cm-php' => 'mode/php',
		'cm-clike' => 'mode/clike',
		
		// Addon - Dialog
		'cm-dialog' => 'addon/dialog/dialog',
		
		// Addon - Search
		'cm-search' => 'addon/search/search',
		'cm-searchcursor' => 'addon/search/searchcursor',
		'cm-match-highlighter' => 'addon/search/match-highlighter',
		
		// Addon - Fold
		'cm-xml-fold' => 'addon/fold/xml-fold',
		
		// Addon - Edit
		'cm-matchtags' => 'addon/edit/matchtags',
		'cm-matchbrackets' => 'addon/edit/matchbrackets',
		'cm-closetag' => 'addon/edit/closetag',
		
		// Addon - Selection
		'cm-active-line' => 'addon/selection/active-line',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'codemirror', $value );
	}
	
	// Localize editor settings
	wp_localize_script( 'cm-main', 'omcAdminEditorSettings', 
		array(
			'themeOptions' => apply_filters( 'omc_codemirror_theme_options', $_omc_codemirror_theme_options ),
		)
	);
	
	// CSS
	$css = array(
		'cm-main' => 'lib/codemirror',
		'cm-dialog' => 'addon/dialog/dialog',
	);
	
	// Loop codemirror theme options
	foreach( apply_filters( 'omc_codemirror_theme_options', $_omc_codemirror_theme_options ) as $theme ){
		$css['cm-theme-'.$theme] = 'theme/'.$theme;
	}
	
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'codemirror', $value );
	}
	
}

/**
 * Helper function to load bootstrap 3 framework
 */
function omc_load_bootstrap(){
	
	$js = array(
		'bootstrap' => 'bootstrap',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'bootstrap', $value );
	}
	
	$css = array(
		'bootstrap' => 'bootstrap',
		//'bootstrap-theme' => 'bootstrap-theme',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'bootstrap', $value );
	}
	
}

/**
 * Helper function to load dropzone
 */
function omc_load_dropzone(){
	
	$js = array(
		'dropzone' => 'dropzone',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'dropzone', $value );
	}
	
	$css = array(
		//'dropzone' => 'dropzone',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'dropzone', $value );
	}
	
}

/**
 * Helper function to load bootstrap datetimepicker
 */
function omc_load_bootstrap_datetimepicker(){
	
	$js = array(
		'bootstrap-datetimepicker' => 'bootstrap-datetimepicker',
		'bootstrap-datetimepicker-config' => 'bootstrap-datetimepicker-config',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'bootstrap-datetimepicker', $value );
	}
	
	$css = array(
		'bootstrap-datetimepicker' => 'bootstrap-datetimepicker',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'bootstrap-datetimepicker', $value );
	}
	
}

/**
 * Helper function to load bootstrap metismenu
 */
function omc_load_bootstrap_metismenu(){
	
	$js = array(
		'bootstrap-metismenu' => 'metisMenu',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'bootstrap-metismenu', $value );
	}
	
	$css = array(
		'bootstrap-metismenu' => 'metisMenu',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'bootstrap-metismenu', $value );
	}
	
}

/**
 * Helper function to load jquery chosen
 */
function omc_load_jquery_chosen(){
	
	$js = array(
		'jquery-chosen' => 'chosen',
		'jquery-chosen-ajax' => 'ajax-chosen',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-chosen', $value );
	}
	
	$css = array(
		'jquery-chosen' => 'chosen',
	);
	foreach( $css as $key => $value ){
		//omc_add_plugin_css( $key, 'jquery-chosen', $value );
	}
	
}

/**
 * Helper function to load jquery chosen
 */
function omc_load_select2(){
	
	$js = array(
		'select2' => 'select2.min',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'select2', $value );
	}
	
	$css = array(
		'select2' => 'select2.min',
		'select2-boostrap' => 'select2.min',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'select2', $value );
	}
	
}

/**
 * Helper function to load jquery datatables
 */
function omc_load_jquery_datatables(){
	
	$js = array(
		'jquery-datatables' => 'jquery.dataTables',
		'bootsrap-datatables' => 'dataTables.bootstrap',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-datatables', $value );
	}
	
	$css = array(
		'bootstrap-datatables' => 'dataTables.bootstrap',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'jquery-datatables', $value );
	}
	
}

/**
 * Helper function to load jquery sortable
 */
function omc_load_jquery_sortable(){
	
	$js = array(
		'jquery-sortable' => 'jquery-sortable',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-sortable', $value );
	}
	
}

/**
 * Helper function to load jquery mousewheel
 */
function omc_load_jquery_mousewheel(){
	
	$js = array(
		'jquery.mousewheel' => 'jquery.mousewheel',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-mousewheel', $value );
	}
	
}

/**
 * Helper function to load jquery mousewheel
 */
function omc_load_jquery_visible(){
	
	$js = array(
		'jquery.visible' => 'jquery.visible',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-visible', $value );
	}
	
}

/**
 * Helper function to load jquery perfect scroll
 */
function omc_load_jquery_perfect_scroll(){
	
	$js = array(
		'jquery-perfect-scroll' => 'perfect-scrollbar.jquery',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-perfect-scroll', $value );
	}
	
	$css = array(
		'jquery-perfect-scroll' => 'perfect-scrollbar',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'jquery-perfect-scroll', $value );
	}
	
}

/**
 * Helper function to load jquery-autosize
 */
function omc_load_jquery_autosize(){
	
	$js = array(
		'jquery-autosize' => 'autosize',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-autosize', $value );
	}	
}

/**
 * Helper function to load jquery-serializeobject
 */
function omc_load_jquery_serializeobject(){
	$js = array(
		'jquery-serializeobject' => 'jquery-serializeobject',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, $key, $value );
	}	
}

/**
 * Helper function to load bootstrap validator
 */
function omc_load_bootstrap_validator(){
	
	$js = array(
		'bootstrap-validator' => 'formValidation.min',
		'bootstrap-validator-framework' => 'bootstrap.min',
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'bootstrap-validator', $value );
	}
	
	$css = array(
		'bootstrap-validator' => 'formValidation.min',
	);
	foreach( $css as $key => $value ){
		omc_add_plugin_css( $key, 'bootstrap-validator', $value );
	}
	
}

/**
 * Helper function to load jquery countdown
 */
function omc_load_jquery_countdown(){
	
	$js = array(
		'jquery-countdown-plugin' => 'jquery.plugin.min',
		'jquery-countdown' => 'jquery.countdown.min',		
	);
	foreach( $js as $key => $value ){
		omc_add_plugin_js( $key, 'jquery-countdown', $value );
	}
	
}









/**
 * Load jquery
 */
add_action( 'wp_enqueue_scripts', 'omc_enqueue_jquery', 5 );
function omc_enqueue_jquery() {
	$url = site_url().'/wp-includes/js/jquery/jquery.js';
	wp_deregister_script( 'jquery' );
	wp_enqueue_script( 'jquery', $url, array(), false, true );
} 

/**
 * Load theme scripts
 */
add_action( 'wp_enqueue_scripts', 'omc_enqueue_theme_scripts', 20 );
function omc_enqueue_theme_scripts() {	
	
	if( is_tablet() )
		wp_enqueue_script( 'main', omc_theme_js_path_url( 'tablet', 'url' ), array(), false, true );
	else if( is_mobile() )
		wp_enqueue_script( 'main', omc_theme_js_path_url( 'mobile', 'url' ), array(), false, true );
	else
		wp_enqueue_script( 'main', omc_theme_js_path_url( 'pc', 'url' ), array(), false, true );
	
	wp_localize_script( 'main', 'info',
		apply_filters( 'omc_info_json', array
		( 
			'siteurl'	=> site_url(),
			'ajaxurl' 	=> admin_url( 'admin-ajax.php?frontend_ajax=' . OMC_FRONTEND_AJAX_HASH ),
			'dir'				=> get_template_directory_uri(),
			'ip'				=> get_user_ip(),
			'is_login' 	=> is_user_logged_in() ? 1 : 0,
			'is_mobile' => is_mobile() ? 1 : 0,
			'is_tablet' => is_tablet() ? 1 : 0,
			'is_phone' => is_phone() ? 1 : 0,
			'is_ios' => is_ios() ? 1 : 0,
			'is_android' => is_android() ? 1 : 0,
			'url_id' => '',
		))
	);
	
	// Add extra scripts for all
	do_action( 'omc_enqueue_theme_scripts_for_all' );
}

// Place omc_before_enqueue_scripts hook
add_action( 'wp_footer', 'omc_before_enqueue_scripts_hook', 10 );
function omc_before_enqueue_scripts_hook(){	
	do_action( 'omc_before_enqueue_scripts' );
}

// Place omc_embed_scripts hook
add_action( 'wp_footer', 'omc_embed_scripts_hook', 40 );
add_action( 'admin_print_footer_scripts', 'omc_embed_scripts_hook', 40 );
function omc_embed_scripts_hook(){
	do_action( 'omc_embed_script' );
}

// Place omc_last_scripts hook
add_action( 'wp_footer', 'omc_last_scripts_hook', 50 );
function omc_last_scripts_hook(){
	do_action( 'omc_last_scripts' );
}

/**
 * Enqueue the scripts used in the admin.
 */
add_action( 'admin_enqueue_scripts', 'omc_load_admin_scripts' );
function omc_load_admin_scripts( $hook_suffix ) {
	
	wp_enqueue_script( 'admin', omc_theme_js_path_url( 'admin', 'url' ), array(), false, true );
	
	// OMC admin enqueue script Hook
	do_action( 'omc_admin_enqueue_scripts', $hook_suffix );
	
}

/**
 * Helper function to load JS plugin
 */
function load_js_plugin( $fn = '' ){
	
	if( empty( $fn ) || !function_exists( $fn ) )
		return false;
		
	add_action( 'wp_enqueue_scripts', $fn, 10 );
	
}

/**
 * Enqueue theme js helper function
 */
function omc_add_theme_js( $file, $depandancy = array( 'jquery' ) ){
	
	$depandancy = (array) $depandancy;
	
	if( !is_array( $file ) )
		$file = omc_pathinfo( $file );
	
	if( empty( $file['filename'] ) )
		return false;
	
	wp_enqueue_script( $file['filename'], omc_path_to_url( $file['file'] ), $depandancy, false, true );
}

/**
 * Enqueue plugin css helper function
 */
function omc_add_plugin_css( $handler, $plugin_name, $filename, $depandancy = array() ){
	wp_enqueue_style( $handler, omc_path_to_url( OMC_JS_PLUGIN_DIR.'/'.$plugin_name.'/'.$filename.'.css' ), $depandancy );
}

/**
 * Enqueue plugin js helper function
 */
function omc_add_plugin_js( $handler, $plugin_name, $filename, $depandancy = array( 'jquery' ) ){
	wp_enqueue_script( $handler, omc_path_to_url( OMC_JS_PLUGIN_DIR.'/'.$plugin_name.'/'.$filename.'.js' ), $depandancy, false, true );
}


/**
 * Helper function to get theme js path/url
 */
function omc_theme_js_path_url( $device, $type = 'path' ){
	return ( $type == 'path' ? CACHES_DIR : CACHES_URL ).'/'.$device.'.js';
}
