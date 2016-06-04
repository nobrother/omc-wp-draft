<?php

namespace OMC;

function admin_init(){
	if ( isset( $_GET['post'] ) )
		$post_id = $post_ID = (int) $_GET['post'];
	elseif ( isset( $_POST['post_ID'] ) )
		$post_id = $post_ID = (int) $_POST['post_ID'];
	else
		$post_id = $post_ID = 0;	

	if ( $post_id )
		$post = get_post( $post_id );



//var_dump($taxnow);
	
}

add_action( 'load-post.php', '\OMC\admin_init' );