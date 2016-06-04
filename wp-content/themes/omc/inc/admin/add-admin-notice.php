<?php
/**
 * Admin notice
 */
 
define( 'OMC_ADMIN_NOTICE', 'omc_admin_notice' );

/*
 * Hook to set admin notice
 */
add_action( 'admin_menu', 'omc_admin_notice' );
function omc_admin_notice() {

	if( empty( $_SESSION[OMC_ADMIN_NOTICE] ) || !is_array( $_SESSION[OMC_ADMIN_NOTICE] ) )
		return;
	
	add_action( 'admin_notices', 'omc_admin_notice_html' );

}

/*
 * Echo admin notice
 */
function omc_admin_notice_html(){
	foreach( $_SESSION[OMC_ADMIN_NOTICE] as $notice ){ ?>
	
	<div class="updated notice is-dismissible">
		<p><?php esc_html_e( $notice ) ?></p>
	</div>
	
	<?php }	
	
	unset( $_SESSION[OMC_ADMIN_NOTICE] );
}

/*
 * Add new admin notice
 */
function omc_add_admin_notice( $notice ){
	$_SESSION[OMC_ADMIN_NOTICE][] = $notice;	
}