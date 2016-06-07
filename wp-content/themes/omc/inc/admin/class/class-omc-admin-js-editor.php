<?php
/**
 * OMC CSS Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_JS_Editor_Settings extends OMC_File_Editor_Settings {

	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_js_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit JS',
				'menu_title'  => 'Edit JS',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit Javascripts', array( 'js' ), OMC_JS_DIR.'/theme' );
		});
		
		// Compile js
		add_action( 'omc_admin_file_editor_after_file_saved', array( $this, 'compile_js' ) );
	}
	
	// Compile js
	function compile_js( $current_file ){
		
		if( 
			is_menu_page( $this->page_id ) &&
			!empty( $current_file ) &&
			!empty( $current_file['extension'] ) &&
			in_array( $current_file['extension'], array( 'js' ) )
		){
			
			compile_js();
		}
	}
}

new OMC_JS_Editor_Settings;