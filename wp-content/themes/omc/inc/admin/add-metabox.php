<?php

define( 'OMC_PAGE_SETTINGS', 'omc_page_settings' );
define( 'OMC_PAGE_SETTINGS_NONCE_FIELD', 'omc_page_settings_nonce' );
define( 'OMC_PAGE_SETTINGS_NONCE_ACTION', 'omc_page_settings_save' );

/**
 * Add page setting metabox
 */
add_action( 'admin_menu', 'omc_add_page_metabox' );
function omc_add_page_metabox() {
	
	add_meta_box( 'omc_page_setting_metabox', 'Page Settings', 'omc_page_setting_metabox_html', 'page', 'normal', 'high' );
}

// Page setting metabox html
function omc_page_setting_metabox_html( $post ){
	wp_nonce_field( OMC_PAGE_SETTINGS_NONCE_ACTION, OMC_PAGE_SETTINGS_NONCE_FIELD );
	
	?>
	<table class="widefat fixed-layout table-layout-label-content">
		<tbody>
			<tr>
				<th><strong>Custom Stylesheet</strong></th>
				<td>
					<input type="text" 
						class="large-text" 
						name="<?php omc_page_settings_name( 'stylesheet' ) ?>" 
						value="<?php esc_attr_e( omc_get_page_settings( 'stylesheet', $post, $post->post_name ) ) ?>" 
					/>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php	
}

/**
 * Save page settings
 */
add_action( 'save_post', 'omc_page_setting_save', 10, 2 );
function omc_page_setting_save( $post_id, $post ){
	
	// Check variable
	if( !isset( $_POST[OMC_PAGE_SETTINGS] ) )
		return false;
	
	// Security check
	if( !wp_verify_nonce( $_POST[OMC_PAGE_SETTINGS_NONCE_FIELD], OMC_PAGE_SETTINGS_NONCE_ACTION ) ){
		omc_add_admin_notice( 'security not pass' );
		return false;		
	}
	
	// Save settings
	omc_set_page_settings( $post );	
}

/**
 * Helper function to echo page settings name
 */
function omc_page_settings_name( $field = '', $echo = true ){
	
	if( $echo )
		esc_attr_e( OMC_PAGE_SETTINGS."[$field]" );
	else
		return esc_attr( OMC_PAGE_SETTINGS."[$field]" );
}

/**
 * Helper function to get page setting
 */
function omc_get_page_settings( $field = '', $post = '', $default = false ){
		
	if( empty( $post ) )
		global $post;
	
	$post_meta = get_post_meta( $post->ID, OMC_PAGE_SETTINGS, true );
	
	if( empty( $field ) )
		return ( $default === false ? $post_meta : $default );
	
	if( isset( $post_meta[$field] ) )
		return $post_meta[$field];
	else
		return ( $default === false ? false : $default );
	
}

/**
 * Helper function to set page settings
 */
function omc_set_page_settings( $post ){
	
	if( empty( $post ) )
		global $post;
	
	// Merge with defaults
	$data = wp_parse_args( $_POST[OMC_PAGE_SETTINGS], array(
		'stylesheet' => $post->post_name,
	) );
	
	return update_post_meta( $post->ID, OMC_PAGE_SETTINGS, $data );
	
}
