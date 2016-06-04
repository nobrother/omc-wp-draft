<?php
/**
 * OMC Template Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_Main_Template_Editor_Settings extends OMC_File_Editor_Settings {

	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_main_template_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit Main Template',
				'menu_title'  => 'Edit Main Template',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Filter file
		add_filter( $this->page_id.'filter_editable_file', array( $this, 'filter_file' ), 10, 4 );
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit Edit Main Template', array( 'php' ), PARENT_DIR );
		});
	}
	
	/**
	 * Filter file
	 */
	public function filter_file( $true_false, $filename, $ext, $dir ){
		return preg_match( '/^(author|category|archive|taxonomy|date|tag|attachment|single|page|home|comments|404|search|index|content)/', $filename );
	}
}

new OMC_Main_Template_Editor_Settings;