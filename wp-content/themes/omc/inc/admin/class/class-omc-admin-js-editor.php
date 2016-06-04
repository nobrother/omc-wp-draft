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
	}
}

new OMC_JS_Editor_Settings;