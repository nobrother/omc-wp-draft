<?php

// Define development mode
define( 'WP_DEV', true );

//* Define Directory Location Constants
define( 'PARENT_DIR', wp_normalize_path( get_template_directory() ) );
define( 'CHILD_DIR', wp_normalize_path( get_stylesheet_directory() ) );
define( 'OMC_IMG_DIR', PARENT_DIR . '/assets/img' );
define( 'OMC_JS_DIR', PARENT_DIR . '/assets/js' );
define( 'OMC_JS_THEME_DIR', PARENT_DIR . '/assets/js/theme' );
define( 'OMC_JS_PLUGIN_DIR', PARENT_DIR . '/assets/js/plugin' );
define( 'OMC_BOOTSTRAP_DIR', PARENT_DIR . '/assets/js-plugin/bootstrap/' );
define( 'OMC_CSS_DIR', PARENT_DIR . '/assets/css' );
define( 'OMC_CSS_THEME_DIR', OMC_CSS_DIR . '/theme' );
define( 'OMC_INC_DIR', PARENT_DIR . '/inc' );
define( 'OMC_ADMIN_DIR', OMC_INC_DIR . '/admin' );
define( 'OMC_ADMIN_CLASS_DIR', OMC_INC_DIR . '/admin/class' );
define( 'OMC_CLASS_DIR', OMC_INC_DIR . '/class' );
define( 'OMC_POST_TYPE_DIR', OMC_INC_DIR . '/post-type' );
define( 'OMC_COOKIES_DIR', OMC_INC_DIR . '/cookies' );
define( 'OMC_STRUCTURE_DIR', OMC_INC_DIR . '/structure' );
define( 'OMC_FUNCTION_DIR', OMC_INC_DIR . '/functions' );
define( 'OMC_SHORTCODE_DIR', OMC_INC_DIR . '/shortcodes' );
define( 'OMC_APPS_DIR', OMC_INC_DIR . '/apps' );
define( 'OMC_TEMPLATE_DIR', PARENT_DIR . '/templates' );
define( 'OMC_COMMON_DIR', OMC_TEMPLATE_DIR . '/common' );
define( 'OMC_SAMPLE_DIR', PARENT_DIR . '/samples' );
define( 'CACHES_DIR', PARENT_DIR . '/caches' );

//* Define URL Location Constants	
define( 'HOME_URL', home_url() );
define( 'PARENT_URL', get_template_directory_uri() );
define( 'CHILD_URL', get_stylesheet_directory_uri() );
define( 'OMC_IMG_URL', PARENT_URL . '/assets/img' );
define( 'OMC_JS_URL', PARENT_URL . '/assets/js' );
define( 'OMC_JS_PLUGIN_URL', PARENT_URL . '/assets/js/plugin' );
define( 'OMC_CSS_URL', PARENT_URL . '/assets/css' );
define( 'OMC_FONTS_URL', OMC_CSS_URL . '/fonts' );
define( 'OMC_INC_URL', PARENT_URL . '/inc' );
define( 'OMC_ADMIN_URL', OMC_INC_URL . '/admin' );
define( 'OMC_CLASS_URL', OMC_INC_URL . '/class' );
define( 'OMC_STRUCTURE_URL', OMC_INC_URL . '/structure' );
define( 'OMC_FUNCTION_URL', OMC_INC_URL . '/functions' );
define( 'OMC_SHORTCODE_URL', OMC_INC_URL . '/shortcodes' );
define( 'CACHES_URL', PARENT_URL . '/caches' );

$upload_dir = wp_upload_dir();
define( 'UPLOAD_URL', $upload_dir['baseurl'] );

//* Define Settings Field Constants (for DB storage)
define( 'OMC_SETTINGS_FIELD', 'omc-settings' );
define( 'OMC_CSS_SETTINGS_FIELD', 'omc-css-settings' );

// Hash
define( 'OMC_FRONTEND_AJAX_HASH', md5( 'omc ajax hash' ) );