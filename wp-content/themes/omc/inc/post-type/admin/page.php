<?php 

namespace OMC\Page\Admin;
use OMC\Page\Page;
use OMC\Page\Custom_Settings;
use OMC\Post_Object_Metabox;
use \WP_Exception as WP_Exception;

/*
 * Metabox
 */
class Metabox extends Post_Object_Metabox {
	
	/**
	 * First time save
	 */
	function first_time_save( $post_id, $post ){
		
		// Load page
		$page = new Page( $post );

		// Create template files
		$page->create_template_files();		
		
		// Add default settings
		$page->set_custom_settings( array( 'stylesheet' => 'page-'.$page->slug ) );
		
	}
	
	/*
	 * Metabox html
	 */
	function settings_html( $post ){
		
		$settings = $this->custom_settings;
		wp_nonce_field( $this->metabox_settings_save_action, $this->metabox_settings_nonce_field );
		
		?>
		<table class="widefat fixed-layout table-layout-label-content">
			<tbody>
				<tr>
					<th><strong>Custom Stylesheets</strong></th>
					<td>
						<input type="text" 
							class="large-text" 
							name="<?php $settings->html_name( 'stylesheet' ) ?>" 
							value="<?php esc_attr_e( $settings->get( 'stylesheet', $post ) ) ?>" 
						/>
					</td>
				</tr>
				
				<tr>
					<th><strong>Create Template files</strong></th>
					<td>
						<button name="save" class="button button-primary button-large" id="btn-reset-template-files" value="1">Do its!</button>
					</td>
				</tr>
				
			</tbody>
		</table>
		<script>
			(function($, global){
				
				/*
				 * EVENTS
				 */
				$(function(){
					
					// Submit to reset template files
					$('#btn-reset-template-files').on('click', function(e){
						
						e.preventDefault();
						
						var overlay = global.overlay;
						
						overlay.addText('Creating...').show(100);
						
						var data = {
							action: 'omc_page_create_template_files',
							post_id: <?php echo $post->ID ?>,
							ajax_hash: '<?php echo Page::HASH ?>'
						};
						
						// Request
						$.post(ajaxurl, data, function(response){
							console.log(response);
							overlay.addText('Done!').hide(100, 300);
						})
					});					
				});
				
			})(jQuery, window);
		</script>
		
		<?php	
	}
}

new Metabox( 'page', new Page() );