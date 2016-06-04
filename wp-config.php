<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'loopman2');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'ld{;]T|.|I+H$Cp!!0:W$!5Cx_ebWFm{oJ[Sm~CRmK-Fqcd$<@#T$z-z?|H-^WkP');
define('SECURE_AUTH_KEY',  '];yRS,l:r2zA f:JH#-_0yW?7oN :7.m$Zi{H</~{q&9_EnIou,To|O2;`QG{7ee');
define('LOGGED_IN_KEY',    'is4{thuc*fMa^+tO`O-Y5g|2v;)dOMU9u)^usN}o-XK(QFyA~UVH{?+FLGna[9rZ');
define('NONCE_KEY',        '&~vwhoDq(2-+9a<WL-yA530x%5;(X`Joe*!qQh/G*@4F#w,C#kRIy-p!=CAP$#6+');
define('AUTH_SALT',        '>K&>29,w(:6muZ!-6gZ}Ed[ re8Io@cnG?ccJ}i56dF;i~J8r|3^+*!F#b^$2W$N');
define('SECURE_AUTH_SALT', '|{+33:X06JY[$4=!78@$#8c9$oVh21$M^:*l7T9j>8?_&Z IeT{CDXU>l[&=W-t_');
define('LOGGED_IN_SALT',   'ZB`[IkubP2/}65z[.+`6OmAxDeFn9d/O^s&>j|b!HjZCp:+AehPD$Ssi^]wsAezv');
define('NONCE_SALT',       '{!-8iw%_BDQ2z[B?mDXE(F$71|UWj-!?K~r6Qa[ _V?0q +&1Wpdr[-SU-- p%pE');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'lpm_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
 
/*
 * Debug
 */
define(	'WP_DEBUG', true);
define( 'WP_DEBUG_LOG', true );

if( WP_DEBUG ){
	@ini_set( 'log_errors', 'On' );
	@ini_set( 'display_errors', 'On' );
	define( 'WP_DEBUG_DISPLAY', true );	
	define( 'SCRIPT_DEBUG', true );		// Load uncompressed javascript and css
} else {
	@ini_set( 'display_errors', 'Off' );
	define( 'WP_DEBUG_DISPLAY', false );
	define( 'DISALLOW_FILE_EDIT', true );
}

/*
 * Cache
 */
//define( 'WP_CACHE', true );

/*
 * Site and home Path and URL 
 */
$path = 'loopman2';
define( 'WP_SITEURL', 'http://'.$_SERVER['HTTP_HOST'].'/'.$path );
define( 'WP_HOME', WP_SITEURL );

/*
 * Wp-content Path and URL 
 */
$wp_content = 'wp-content';
define( 'WP_CONTENT_DIR', dirname(__FILE__).'/'.$wp_content );
define( 'WP_CONTENT_URL', WP_SITEURL.'/'.$wp_content );

/*
 * Plugins Path and URL 
 */
$plugins = 'plugins';
define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR.'/'.$plugins );
define( 'WP_PLUGIN_URL', WP_CONTENT_URL.'/'.$plugins );

/* 
 * Uploads Path and URL 
 *
 * This path can not be absolute. 
 * It is always relative to ABSPATH, 
 * therefore does not require a leading slash
 */
define( 'UPLOADS', $wp_content.'/uploads' );

/*
 * Post Revisions
 */
define( 'WP_POST_REVISIONS', 10 );	// Or false to disable

/*
 * Empty Trash
 */
if( WP_DEBUG ){
	define( 'EMPTY_TRASH_DAYS', 0 ); // 0 to disable
}

/*
 * Block External URL Requests
 */
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
define( 'WP_ACCESSIBLE_HOSTS', 'api.wordpress.org,*.github.com' );

unset( $path, $wp_content, $plugins );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
