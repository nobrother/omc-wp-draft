<?php
/**
 * OMC Theme Settings
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_Theme_Settings extends OMC_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 1.8.0
	 *
	 * @uses OMC_ADMIN_IMAGES_URL     URL for admin images.
	 * @uses OMC_SETTINGS_FIELD       Settings field key.
	 * @uses PARENT_DB_VERSION            OMC database version.
	 * @uses PARENT_THEME_VERSION         OMC Framework version.
	 * @uses omc_get_default_layout() Get default layout.
	 * @uses \OMC_Admin::create()     Create an admin menu item and settings page.
	 */
	function __construct() {

		$page_id = 'omc_theme_settings';

		$menu_ops = apply_filters(
			'omc_theme_settings_menu_ops',
			array(
				'main_menu' => array(
					'sep' => array(
						'sep_position'   => '58.995',
						'sep_capability' => 'edit_theme_options',
					),
					'page_title' => 'Theme Settings',
					'menu_title' => 'Theme Settings',
					'capability' => 'edit_theme_options',
					'icon_url'   => 'dashicons-carrot',
					'position'   => '58.996',
				),
				'first_submenu' => array( //* Do not use without 'main_menu'
					'page_title' => 'Theme Settings',
					'menu_title' => 'Theme Settings',
					'capability' => 'edit_theme_options',
				),
			)
		);

		$page_ops = apply_filters(
			'omc_theme_settings_page_ops',
			array(
				'screen_icon'       => 'options-general',
				'save_button_text'  => 'Save Settings',
				'reset_button_text' => 'Reset Settings',
				'saved_notice_text' => 'Settings saved.',
				'reset_notice_text' => 'Settings reset.',
				'error_notice_text' => 'Error saving settings.',
			)
		);

		$settings_field = OMC_SETTINGS_FIELD;
		
		
		
		$default_settings = apply_filters(
			'omc_theme_settings_defaults',
			array(
				'header_scripts' => "",
				'header_scripts_after_head' => 0,
				'footer_scripts' => '',
				'footer_scripts_after_footer' => 0,				
				'ctrl_s_save_post' => 0,
				'ctrl_s_save_menu' => 0,
				'disable_rich_editing' => 1,
			)
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		add_action( 'omc_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );
		
		// Enqueue code mirror scripts
		add_action( 'omc_admin_enqueue_scripts', array( $this, 'load_editor_scripts' ) );
	}
	
	/**
	 * Load editor javascripts
	 */
	public function load_editor_scripts(){
		if( is_menu_page( $this->page_id ) )
			omc_load_codemirror();
	}

	/**
	 * Register each of the settings with a sanitization filter type.
	 *
	 * @since 1.7.0
	 *
	 * @uses omc_add_option_filter() Assign filter to array of settings.
	 *
	 * @see \OMC_Settings_Sanitizer::add_filter() Add sanitization filters to options.
	 */
	public function sanitizer_filters() {
		
		omc_add_option_filter(
			'one_zero',
			$this->settings_field,
			array(
				'ctrl_s_save_post',
				'disable_rich_editing',
				'header_scripts_after_head',
				'footer_scripts_after_footer',
				
			)
		);
		
		omc_add_option_filter(
			'requires_unfiltered_html',
			$this->settings_field,
			array(
				'header_scripts',
				'footer_scripts',
			)
		);

	}

	/**
 	 * Register meta boxes on the Theme Settings page.
 	 */
	function metaboxes() {
		
		// Scripts
		add_meta_box( 'omc-theme-settings-scripts', 'Scripts For Every Page', array( $this, 'scripts_box' ), $this->pagehook, 'main' );
		
		// Ctrl + S
		add_meta_box( 'omc-theme-settings-ctrl-s', 'Enable Ajax Ctrl + S', array( $this, 'ctrl_s_box' ), $this->pagehook, 'main' );
		
		// Rich editing
		add_meta_box( 'omc-theme-settings-disable-rich-editing', 'Disable Visual Editor', array( $this, 'disable_rich_editing' ), $this->pagehook, 'main' );
			
		// Additional metabox goes here	
		do_action( 'omc_theme_settings_metaboxes', $this->pagehook );

	}

	/**
	 * Callback for Theme Settings Header / Footer Scripts meta box.
	 */
	function scripts_box() { ?>
	
		<p><span class="description">This script will print before any other script</span></p>
		
		<?php 
		
		if( !( $top_script = $this->get_field_value( 'top_script' ) ) )
			$top_script = "<script>
	var trackingCode = 'UA-44426442-XX';
			window['ga-disable-' + trackingCode] = true;
		
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName('head')[0];a.async=1;a.src=g;m.appendChild(a)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', trackingCode, 'auto');
	ga('send', 'pageview');

</script>";
		
		?>
		
		<textarea name="<?php echo $this->get_field_name( 'top_script' ); ?>" id="<?php echo $this->get_field_id( 'top_script' ); ?>" cols="78" rows="8" class="code-editor" data-mode="html" data-height="100px"><?php echo esc_textarea( $top_script ); ?></textarea>

		<hr class="div" />

		<p><span class="description">This is the last script to print.</span></p>
		
		<textarea name="<?php echo $this->get_field_name( 'bottom_script' ); ?>" id="<?php echo $this->get_field_id( 'bottom_script' ); ?>" cols="78" rows="8" class="code-editor" data-mode="html" data-height="100px"><?php echo esc_textarea( $this->get_field_value( 'bottom_script' ) ); ?></textarea>

		<?php

	}
	
	
	/**
	 * Callback for Theme Settings Ctrl + S meta box.
	 */
	function ctrl_s_box(){ ?>
	
		<p><span class="description">Ctrl + S default behavior had been disabled in entire admin page. Instead of saving the html page, ctrl + s is meaned to submit the current form or do nothing if such the form is not exists. Below options enable form submission with ajax when ctrl + s is hit.</span></p>
		
		<p>
			<input type = "checkbox" name="<?php echo $this->get_field_name( 'ctrl_s_save_post' ); ?>" id="<?php echo $this->get_field_id( 'ctrl_s_save_post' ); ?>" value="1"<?php checked( $this->get_field_value( 'ctrl_s_save_post' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'ctrl_s_save_post' ); ?>"><?php echo 'On post editing?' ?></label>
		</p>
		
		<p>
			<input type = "checkbox" name="<?php echo $this->get_field_name( 'ctrl_s_save_menu' ); ?>" id="<?php echo $this->get_field_id( 'ctrl_s_save_menu' ); ?>" value="1"<?php checked( $this->get_field_value( 'ctrl_s_save_menu' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'ctrl_s_save_menu' ); ?>"><?php echo 'On menu page?' ?></label>
		</p>
		
		<?php
	}
	
	/**
	 * Callback for Theme Settings Disable visual editor meta box.
	 */
	function disable_rich_editing(){ ?>
		
		<p>
			<input type = "checkbox" name="<?php echo $this->get_field_name( 'disable_rich_editing' ); ?>" id="<?php echo $this->get_field_id( 'disable_rich_editing' ); ?>" value="1"<?php checked( $this->get_field_value( 'disable_rich_editing' ) ); ?> />
			<label for="<?php echo $this->get_field_id( 'disable_rich_editing' ); ?>"><?php echo 'Yes, disable it please.' ?></label>
		</p>
		
		<?php
	}
	
}

// Initiate
new OMC_Theme_Settings;