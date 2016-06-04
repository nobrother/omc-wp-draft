<?php
/**
 * OMC CSS Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_CSS_Editor_Settings extends OMC_File_Editor_Settings {
	
	public $_less = null;
	
	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_css_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit CSS',
				'menu_title'  => 'Edit CSS',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit CSS / LESS', array( 'css', 'less' ), OMC_CSS_DIR.'/theme' );
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

new OMC_CSS_Editor_Settings;