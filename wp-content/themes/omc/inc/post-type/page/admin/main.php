<?php 

namespace OMC\Post_Type\Page\Admin;
use OMC\Post_Type\Abstract_Admin;
use OMC\Post_Type\Page\Object;
use \WP_Exception as WP_Exception;

/*
 * Metabox
 */
class Main extends Abstract_Admin {
	
	protected static $post_type = 'page';
	protected static $object_class = '\OMC\Post_Type\Page\Object';
	protected static $default_meta;	
	
	/*
	 * Add meta box
	 */ 
	static function add_extra_metaboxs( $post_type, $post ) {
		
		add_meta_box( "omc_{$post_type}_setting_metabox", 'setting', array( get_called_class(), 'settings_html' ), $post_type, 'normal', 'high' );
	}
	
	/**
	 * First time save
	 */
	static function first_time_save( $post_id, $post ){
		
		// Load page
		$page = new Object( $post );

		// Create template files
		$page->create_template_files();		
	}
	
	/*
	 * Metabox html
	 */
	static function settings_html( $post ){	?>
		<table class="widefat fixed-layout table-layout-label-content">
			<tbody>				
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

// Initialize
Main::init();