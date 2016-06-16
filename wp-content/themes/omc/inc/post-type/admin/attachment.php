<?php 

namespace OMC\Attachment\Admin;
use OMC\Attachment\Object;
use OMC\Attachment\Custom_Settings;
use OMC\Post_Object_Metabox;
use \WP_Exception as WP_Exception;

/*
 * Metabox
 */
class Metabox extends Post_Object_Metabox {
	
	/*
	 * Construct
	 */
	function __construct( $post_type = '', $object = '' ){
		parent::__construct( $post_type, $object ); 
		
		add_action( 'attachment_updated', array( $this, 'save' ), 10, 2 );
		//add_action( 'admin_init', array( $this, 'remove_post_support' ) );
		//add_action( 'add_meta_boxes', array( $this, 'add_metaboxs' ), 10, 2 );
		//add_action( 'omc_admin_enqueue_scripts', array( $this, 'load_editor_scripts' ) );
	}
}

new Metabox( 'attachment', new Object() );