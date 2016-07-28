<?php
use \WP_Exception as e;
ignore_user_abort(true);

define( 'DOING_API', true );
//require_once( '../../wp-load.php' );

/*
$action = 'omc_user_change_password';
omc_nonce_field( $action );
$_POST['omc_nonce'] = wp_create_nonce( sha1( $action.Cookie::Get( 'unique_user_id' ) ) );
var_dump(wp_verify_nonce( $_POST['omc_nonce'], sha1( $action.Cookie::Get( 'unique_user_id' ) ) ));
*/


/*
 * Post Object Metabox Class
 
abstract class Abstract_Admin {
	
	// Should define in child class
	protected static $post_type;
	
	static function debug(){
		var_dump( static::$post_type );
		var_dump( get_called_class() );
	}
	
	/*
	 * Construct
	 
	static function init( $post_type = '', $object_class = '' ){
		
	}
}

class Main extends Abstract_Admin { 
	//protected static $post_type = 'haha';
	
}
class Main2 extends Abstract_Admin { protected static $post_type = 'page'; }
Main::init();
Main2::init();
Main::debug();
Main2::debug();
*/

declare(ticks=1);

// A function called on each tick event
function tick_handler()
{
    echo "tick_handler() called\n";
}

register_tick_function('tick_handler');

$a = 1;

if ($a > 0) {
    $a += 2;
    print($a);
}
die();