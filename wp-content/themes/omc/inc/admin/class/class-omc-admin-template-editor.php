<?php
/**
 * OMC Template Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_Template_Editor_Settings extends OMC_File_Editor_Settings {

	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_template_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit Template',
				'menu_title'  => 'Edit Template',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit Template', array( 'php', 'less', 'js' ), OMC_TEMPLATE_DIR );
		});
		
		// Compile less
		add_action( 'omc_admin_file_editor_after_file_saved', array( $this, 'compile_less' ) );
	}
	
	// Compile less
	function compile_less( $current_file ){
		
		if( 
			is_menu_page( $this->page_id ) &&
			!empty( $current_file ) &&
			!empty( $current_file['extension'] ) &&
			in_array( $current_file['extension'], array( 'css', 'less' ) )
		){
			compile_less();
		}
	}
}

new OMC_Template_Editor_Settings;