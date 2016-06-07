<?php

/**
 * OMC general functions
 */

/**
 * Debug
 */
function _log( $message ) {
	if( WP_DEBUG === true ){
		if( is_array( $message ) || is_object( $message ) )
			error_log( print_r( $message, true ) );
		else
			error_log( $message );
	}
}

/**
 * wordpress-style function to start the session when net started.
 */
function maybe_start_session(){
	$ssid = session_id();
	
	if ( empty( $ssid ) )
		session_start();
}

/**
 * Exit with 404
 */
function throw_404(){

	global $wp_query;

	$wp_query->set_404();
	status_header(404);
	include( get_404_template() );
	die();
}



/**
 * Striptslash because wordpress add magic quote
 * Set hook on init might not an good idea
 * At least the editor in in admin page will need the magic quote
 */
function omc_stripslash() {
	$_POST      = array_map( 'stripslashes_deep', $_POST );
	$_GET       = array_map( 'stripslashes_deep', $_GET );
	$_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
	$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
}

/*
 * Create rnd str
 */
function omc_rndstr( $length = 6 ) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	
	for ( $i = 0; $i < $length; $i++ ) 
	$randomString .= $characters[rand(0, $charactersLength - 1)];

	return $randomString;
}

/*
 * Check if it is valid url
 */
function is_url( $url = '' ){
	return ( !filter_var( $url, FILTER_VALIDATE_URL) === false );
}

/*
 * Current URL
 */
function get_current_url(){
	return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

/*
 * Current URL path ( without root )
 */
function get_current_url_path( $url = '' ){

	if( !empty( $url ) )
		return str_replace( home_url(), '', $url );

	else
		return str_replace( home_url(), '', get_current_url() );
}

/*
 * Remove url query
 */

function remove_url_query( $url = '' ){

	if( empty( $url ) )
		$url = get_current_url();

	return parse_url( $url, PHP_URL_PATH );
}



/*
 * Get url query
 */
function get_url_query( $url = '' ){

	if( empty( $url ) )
		$url = get_current_url();

	return parse_url( $url, PHP_URL_QUERY );
}



/*
 * Compare url without query by default
 * Full compare $compare = array( 'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment' )
 */
function compare_url( $url1, $url2 = '', $compare = array( 'host', 'path' ) ){

	$url1 = parse_url( $url1 );
	$url2 = !empty( $url2 ) ?  parse_url( $url2 ) : parse_url( get_current_url() ) ;

	foreach( $compare as $part ){
		if( !isset( $url1[$part], $url2[$part] ) || $url1[$part] !== $url2[$part] ){
			//var_dump($url1[$part]);
			//var_dump($url2[$part]);
			return false;
		}
	}

	return true;
}



/*
 * Get image url
 * Only for when year/month directory disabled
 */
function get_upload_url( $name ){
	$upload_dir = wp_upload_dir()['baseurl'];
	return $upload_dir.'/'.$name;
}



/*
 * Json encode without quote options
 * add '@' at the front of the value to remove the quote
 */
function encode_json_with_quote_control( $arr ){
	$tmp = array();

	$arr = array_map_recursive( function( $d ) use ( &$tmp ){

		if( substr( $d, 0, 1 ) == '@' ){
			$tmp[] = substr( $d, 1 );
			return '%s';
		}

		return $d;

	}, $arr );

	return vsprintf( str_replace( '"%s"', '%s', json_encode( $arr ) ), $tmp );
}

/*
 * Go through every element in array
 */
function array_map_recursive( callable $func, array $arr) {
	array_walk_recursive( $arr, function(&$v) use ( $func ) {
		$v = $func($v);
	});

	return $arr;
}

/*
 * Get User IP
 */
function get_user_ip() {

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {

		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];

	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {

		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

	} else {

		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return apply_filters( 'omc_get_ip', $ip );
}

/*
 * Detect device, os and browser
 */
function detect( $action = '' ){

	global $_detect;

	// First time
	if( empty( $_detect ) ){

		// Load Class is not exists
		if( !class_exists( 'Mobile_Detect' ) ){

			@ require_once OMC_APPS_DIR.'/mobile-detect/Mobile_Detect.php';
			
			if( !class_exists( 'Mobile_Detect' ) )
				return false;
		}

		$_detect = new Mobile_Detect;
	}	

	// Find cache
	if( property_exists( $_detect, $action ) )
		return $_detect->$action;

	// Detect
	switch( $action ){
		case 'is_mobile':	return $_detect->$action = $_detect->isMobile();
		case 'is_tablet':	return $_detect->$action = $_detect->isTablet();
		case 'is_phone': return $_detect->$action = ( $_detect->isMobile() && !$_detect->isTablet() );
		case 'is_ios': return $_detect->$action = $_detect->isiOS();
		case 'is_android': return $_detect->$action = $_detect->isAndroidOS();
	}

	return false;
}

/*
 * Helper function Detect device, os and browser
 */
function is_mobile(){
	return detect( 'is_mobile' );
}

function is_tablet(){
	return detect( 'is_tablet' );
}

function is_phone(){
	return detect( 'is_phone' );
}

function is_ios(){
	return detect( 'is_ios' );
}

function is_android(){
	return detect( 'is_android' );
}

/*
 * Convert array to key1=value1&key2=value2 style
 */

function array_to_query( $data ){
	if( !empty( $data ) ){
		$output = '';
		foreach( $data as $key => $value )
			$output .= '&' . urlencode( $key ) . '=' . urlencode( $value );

		return substr( $output, 1 );
	}

	return '';
}


/**
 * Retrieve the name of the highest priority template file that exists.
 */
function locate_file( $files = array() ) {

	if( empty( $files ) )
		return false;
	
	foreach ( (array) $files as $file ) {
		if ( file_exists( $file ) )
			return omc_pathinfo( $file );
	}

	return false;
}

/**
 * Return file path info
 * Example: '/www/htdocs/inc/lib.inc.php'
 * return = array(
		'dirname' => '/www/htdocs/inc'
		'basename' => 'lib.inc.php'
		'extension' => 'php'
		'filename' => 'lib.inc'
		'file' => '/www/htdocs/inc/lib.inc.php'
		'home_path' => '/inc/lib.inc.php'
	 )
 */
function omc_pathinfo( $path = '' ) {
	
	if( empty( $path ) )
		return false;
	
	$abspath = wp_normalize_path( ABSPATH );	
	$path = wp_normalize_path( $path );
		
	return pathinfo( $path ) + array( 'file' => $path, 'home_path' => str_replace( $abspath, '', $path ) );
}

/**
 * Path to url
 */
function omc_path_to_url( $path = '' ) {
	
	if( empty( $path ) )
		return false;
	
	$file = omc_pathinfo( $path );
	return home_url( $file['home_path'] );
}


/*
 * Check ajax is from frontend or backend or none
 * @return 'front', 'back' and 'none'
 */
function omc_is_ajax(){
 
	// Is in ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ){	
		if( isset( $_REQUEST['action'] ) && strpos( $_REQUEST['action'], 'omc' ) === 0 ){
			if( isset( $_GET['frontend_ajax'] ) && $_GET['frontend_ajax'] == OMC_FRONTEND_AJAX_HASH )
				return 'front';
			else
				return 'back';	
		} else {
			return 'none_omc_ajax';
		}	 	 
	}

	// Not in ajax
	else
	 return 'none';
}
function is_omc_ajax(){
	return in_array( omc_is_ajax(), array( 'front', 'back' ) );
}

/* 
 * List all the files and folders in a Directory 
 * List down all files, then all directories
 */
function scandir_recursive( $dir, $is_pathinfo = false, $preg_pattern = false, &$results = array() ){
    $files = scandir( $dir );
		$dirs = array();
		
    foreach( $files as $filename ){
			
			if( $filename != '.' 
					&& $filename != '..' 
			){
				
				$path = wp_normalize_path( $dir.'/'.$filename );
				
				// Store directory path for later use
				if( is_dir( $path ) )
					$dirs[] = $path;
				
				else {
					if( $preg_pattern !== false && !preg_match( $preg_pattern, $path ) )
						continue;		
					if( $is_pathinfo )
						$results[] = omc_pathinfo( $path );
					else
						$results[] = $path;
				}
			}			
    }
		
		foreach( $dirs as $dir ){
			scandir_recursive( $dir, $is_pathinfo, $preg_pattern, $results );
		}

    return $results;
}

/* 
 * Generate common less object
 */
function less(){
	if( !class_exists( 'lessc' ) )
		require_once OMC_APPS_DIR.'/lessc/lessc.inc.php';
		
	$less = new lessc;
	$less->setFormatter( 'compressed' );		
	
	/*==========================
	 * Register custom function
	 *==========================*/
	// roundup(@var, @multiple)
	$less->registerFunction( 'roundup' , function( $arg ) {
		
		// Exit is arguement is not correct
		if( empty( $arg[2] ) || empty( $arg[2][0] ) || empty( $arg[2][1] ) || 
				empty( $arg[2][0][0] ) || empty( $arg[2][1][0] ) || 
				$arg[2][0][0] !== 'number' || $arg[2][1][0] !== 'number' 
			)
			return $arg;
		
		// Get argument
		$var = $arg[2][0];
		$multiple = floor( $arg[2][1][1] ); // Make sure multiple is integer
		
		// Output
		list( $type, $value, $unit ) = $var;
		return array( $type, $multiple * ceil( $value / $multiple ), $unit );
	} );
	
	return $less;
}

/* 
 * Compile Less in OMC style
 */
function compile_less(){
	
	// General mixin
	$pc_style = $mobile_style = $tablet_style = array( 
		OMC_CSS_THEME_DIR.'/mixin/mixin-main.less', 
		OMC_CSS_THEME_DIR.'/mixin/mixin-site.less',
	);
	
	// Mixin
	$pc_style[] = OMC_CSS_THEME_DIR.'/mixin/mixin-pc.less';
	$mobile_style[] = OMC_CSS_THEME_DIR.'/mixin/mixin-mobile.less';
	$tablet_style[] = OMC_CSS_THEME_DIR.'/mixin/mixin-tablet.less';
	$admin_style[] = OMC_CSS_THEME_DIR.'/others/admin.less';
	
	// Devices
	$pc_style[] = OMC_CSS_THEME_DIR.'/devices/pc.less';
	$mobile_file = OMC_CSS_THEME_DIR.'/devices/mobile.less';
	$tablet_file = OMC_CSS_THEME_DIR.'/devices/tablet.less';
	if( file_exists( $mobile_file ) ){
		$mobile_style[] = $mobile_file;
		$tablet_style[] = file_exists( $tablet_file ) ? $tablet_file : $mobile_file;						
	} else {
		$mobile_style[] = OMC_CSS_THEME_DIR.'/devices/pc.less';
		$tablet_style[] = file_exists( $tablet_file ) ? $tablet_file : OMC_CSS_THEME_DIR.'/devices/pc.less';
	} 
	
	// Templates
	$files = scandir_recursive( OMC_TEMPLATE_DIR, true, '/style\.less$/' );	
	
	foreach( $files as $file ){
		$pc_style[] = $file['file'];
		$mobile_file = $file['dirname'].'/'.$file['filename'].'-mobile.less';
		$tablet_file = $file['dirname'].'/'.$file['filename'].'-tablet.less';
		if( file_exists( $mobile_file ) ){
			$mobile_style[] = $mobile_file;
			$tablet_style[] = file_exists( $tablet_file ) ? $tablet_file : $mobile_file;						
		} else {
			$mobile_style[] = $file['file'];
			$tablet_style[] = file_exists( $tablet_file ) ? $tablet_file : $file['file'];					
		}				
	}
	
	// Generate pc.css, mobile.css, tablet.css
	foreach( array( 'pc', 'mobile', 'tablet', 'admin' ) as $type ){
		$var = $type.'_style';
		$input_file = CACHES_DIR.'/'.$type.'.less';
		$cache_file = CACHES_DIR.'/'.$type.'.less.cache';
		$output_file = omc_theme_css_path_url( $type );
		
		$content = '@import "'.implode( '";'.PHP_EOL.'@import "', $$var ).'";';
		if( file_exists( $input_file ) && file_get_contents( $input_file ) !== $content || !file_exists( $input_file ) )
			file_put_contents( $input_file, $content );
		
		if ( file_exists( $cache_file ) ) {
			$cache = unserialize( file_get_contents( $cache_file ) );
		} else {
			$cache = $input_file;
		}

		$new_cache = less()->cachedCompile( $cache );
		
		if ( !is_array( $cache ) || $new_cache['updated'] > $cache['updated'] ) {
			file_put_contents( $cache_file, serialize( $new_cache ) );
			file_put_contents( $output_file, $new_cache['compiled'] );
		}
	}
}

/* 
 * Compile Javascipts in OMC style
 */
function compile_js(){
	$scripts_list = apply_filters( 'omc_enqueue_theme_scripts_list', array( 'main', 'site' ) );
	$pc_js = $mobile_js = $tablet_js = array_map( function( $value ){
		return OMC_JS_THEME_DIR.'/'.$value.'.js';
	}, $scripts_list );
	
	$admin_scripts_list = apply_filters( 'omc_enqueue_admin_scripts_list', array( 'admin', 'admin-editor' ) );
	$admin_js = array_map( function( $value ){
		return OMC_JS_THEME_DIR.'/'.$value.'.js';
	}, $admin_scripts_list );
	
	// Templates
	$files = scandir_recursive( OMC_TEMPLATE_DIR, true, '/extend\.js$/' );	
	
	foreach( $files as $file ){
		$pc_js[] = $file['file'];
		$mobile_file = $file['dirname'].'/'.$file['filename'].'-mobile.js';
		$tablet_file = $file['dirname'].'/'.$file['filename'].'-tablet.js';
		if( file_exists( $mobile_file ) ){
			$mobile_js[] = $mobile_file;
			$tablet_js[] = file_exists( $tablet_file ) ? $tablet_file : $mobile_file;						
		} else {
			$mobile_js[] = $file['file'];
			$tablet_js[] = file_exists( $tablet_file ) ? $tablet_file : $file['file'];					
		}	
	}
	
	// Combine and minify
	$cache = array();
	foreach( array( 'pc', 'mobile', 'tablet', 'admin' ) as $type ){
		$var = $type.'_js';
		$f = fopen( omc_theme_js_path_url( $type ), 'w+' );
		foreach( $$var as $file ){
			
			if( !isset( $cache[$file] ) ){
				
				$cache_file = CACHES_DIR.'/'.md5( $file.filemtime( $file ) ).'.js.cache';
				
				if( file_exists( $cache_file ) )
					$cache[$file] = file_get_contents( $cache_file );
				
				else{
					if( !class_exists( '\JShrink\Minifier' ) )
						require_once OMC_APPS_DIR.'/JShrink/Minifier.php';
					
					// Store cache
					$cache[$file] = \JShrink\Minifier::minify( file_get_contents( $file ), array( 'flaggedComments' => true ) );
					$cache_file = file_put_contents( $cache_file, $cache[$file] );
				}
			}
			fwrite( $f, $cache[$file] );			
		}
		fclose( $f );
	}
}