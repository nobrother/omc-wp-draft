<?php
/**
 * OMC Template Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_Component_Editor_Settings extends OMC_File_Editor_Settings {

	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_component_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit Component',
				'menu_title'  => 'Edit Component',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit Component', array( 'php' ), OMC_COMPONENT_DIR );
		});
	}
}

new OMC_Component_Editor_Settings;