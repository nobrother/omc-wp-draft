<?php
/**
 * OMC Template Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_Header_Footer_Editor_Settings extends OMC_File_Editor_Settings {

	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_header_footer_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit Header & Footer',
				'menu_title'  => 'Edit Header & Footer',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Filter file
		add_filter( $this->page_id.'filter_editable_file', array( $this, 'filter_file' ), 10, 4 );
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit Header & Footer', array( 'php' ), OMC_COMPONENT_DIR );
		});
	}
	
	/**
	 * Filter file
	 */
	public function filter_file( $true_false, $filename, $ext, $dir ){
		return preg_match( '/^(header|footer|sidebar|nav)/', $filename );
	}
}

new OMC_Header_Footer_Editor_Settings;