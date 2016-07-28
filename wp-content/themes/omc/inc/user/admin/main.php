<?php
namespace OMC\User\Admin;
use OMC\User\Object;
use \WP_Exception as e;

class Main{

	/*
	 * Initialize
	 */
	static function init(){
		
		// Actions
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'omc_user_reload_capbilities', array( __CLASS__, 'manage_capabilities' ) );
	}
	
	// Manage capabilities
	static function manage_capabilities(){
		
		$subscriber = get_role( 'subscriber' );
		
		// Upload file capability
		$subscriber->add_cap( 'upload_files' );
	}
	
	// Add subpage in user menu
	static function add_settings_page( $info ){
		add_users_page( 'User Settings', 'Settings', 'manage_options', 'omc_user_settings_page', array( __CLASS__, 'settings_html' ) );
	}
	
	// Users settings page html
	static function settings_html(){ 
		global $current_user;
		$obj = new Object( $current_user );
	?>
		<h1>User Settings</h1>
		<h2>Actions</h2>
		<form id="form-omc-user-settings-reload-capabilities">
			<?php omc_nonce_field( 'omc_user_reload_capabilities', $obj->email ); ?>
			<input type="hidden" name="uid" value="<?php echo $obj->id ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th>Reload roles and capabilities</th>
						<td><button type="submit" class="button button-secondary">Do it!</button></td>
					<tr>
				</tbody>				
			</table>
		</form>
		<script>
			(function($, global){
				
				/*
				 * EVENTS
				 */
				$(function(){
					
					// Submit to reset template files
					$('#form-omc-user-settings-reload-capabilities').on('submit', function(e){
						
						e.preventDefault();
						
						var overlay = global.overlay;
						
						overlay.addText('Reloading...').show(100);
						
						var data = $.extend({}, $(this).serializeObject(), { action: 'omc_user_reload_capabilities' });
						
						// Request
						$.post(ajaxurl, data, function(response){
							console.log(response);
							overlay.addText('Done!').hide(100, 300);
						})
					});					
				});
				
			})(jQuery, window);
		</script>
	<?php }
}

Main::init();