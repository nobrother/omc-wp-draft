<?php
use OMC\Attachment\Object as Attachment;

defined( 'ABSPATH' ) or die( "Don't try to do silly things!" );

global $post, $attachment;
$attachment = new Attachment( $post );

if( wp_attachment_is_image( get_the_ID() ) ){
	include 'layout.php';
	
} else {
	switch( get_post_mime_type( get_the_ID() ) ){
		case 'application/pdf':
		
		break;
		
		default:
			
	}
}