<?php
/**
 * OMC CSS Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

class OMC_Bootstrap_Less_Editor_Settings extends OMC_File_Editor_Settings {

	/**
	 * Create an admin menu item and settings page.
	 */
	function __construct() {

		$this->page_id = 'omc_bootstrap_less_editor';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'omc_theme_settings',
				'page_title'  => 'Edit Bootstrap',
				'menu_title'  => 'Edit Bootstrap',
			),
		);

		$this->create( $this->page_id, $menu_ops );
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
		// Generate Bootstrap
		add_action( 'admin_init', array( $this, 'gen_bootstrap' ) );
		
		// Print editor
		add_action( $this->page_id.'_file_editor_html', function(){
			$this->print_editor( 'Edit Bootstrap Less', array( 'css', 'less' ), OMC_JS_PLUGIN_DIR.'/bootstrap/less' );
		});
	}
	
	/**
	 * Save the new content
	 */
	public function save_file(){
		
		// Check on the right page		
		if( ! is_menu_page( $this->page_id ) )
			return;
		
		// Security check
		if( ! ( 
						isset( $_POST['action'], $_POST['edit_file'], $_POST['_wpnonce'] ) 
						&& $_POST['action'] == 'omc_edit_file'
						&& wp_verify_nonce( $_POST['_wpnonce'], 'omc_edit_file'.$_POST['edit_file'] ) 
					) 
			)
			return;
		
		// Get current file
		$current_file = array(
			'file' => stripslashes( $_POST['edit_file'] ),
			'name' => stripslashes( $_POST['edit_file_name'] ),
			'ext' => stripslashes( $_POST['edit_file_ext'] ),
			'path' => stripslashes( $_POST['edit_file_path'] ),
		);
		
		$newcontent = wp_unslash( $_POST['newcontent'] );
		
		$filename = $current_file['path'].'/'.$current_file['file'];
		
		if ( is_writeable( $filename ) ) {
			
			// Write new content to file
			$f = fopen( $filename, 'w+');
			fwrite($f, $newcontent);
			fclose($f);
			
			// Sets access and modification time of file
			touch( $filename );
			
		}
		
		die(); // because it is and ajax request
	}
	
	/**
	 * Generate bootstrap
	 */
	public function gen_bootstrap(){
		
		// Check on the right page		
		if( ! is_menu_page( $this->page_id ) )
			return;
		
		// Security check
		if( ! ( 
						isset( $_POST['action'], $_POST['_wpnonce'] ) 
						&& $_POST['action'] == 'omc_gen_bootstrap'
						&& wp_verify_nonce( $_POST['_wpnonce'], 'omc_gen_bootstrap' ) 
					) 
			)
			return;
			
		// Compile and caching
		require_once OMC_APPS_DIR.'/less.php/lessc.inc.php';
		require_once OMC_APPS_DIR.'/less.php/lib/Less/Exception/Parser.php';
		
		$cache_dir = OMC_JS_PLUGIN_DIR.'/bootstrap/cache';
		Less_Cache::$cache_dir = $cache_dir;
		
		$options = array( 'compress' => false );
		$less_files = array(
			OMC_JS_PLUGIN_DIR.'/bootstrap/less/bootstrap.less' => 'bootstrap.less',
		);
		$css_file_name = Less_Cache::Get( $less_files, $options );
		
		copy( $cache_dir.'/'.$css_file_name, OMC_JS_PLUGIN_DIR.'/bootstrap/bootstrap.css' );
		
		// Sets access and modification time of file
		touch( OMC_JS_PLUGIN_DIR.'/bootstrap/bootstrap.css' );
		
		die(); // because it is and ajax request
	}
	
	/**
	 * Editor html
	 */
	public function print_editor( $title = "Edit file", $editable_extensions, $dir ){
				
		// Get file name
		$main_files = scandir( $dir );
		$mixin_files = scandir( $dir.'/mixins' );
		$raw_files = array( '/' => $main_files, '/mixins/' => $mixin_files );
		
		if( empty( $raw_files ) )
			wp_die(sprintf('<p>%s</p>', 'Cant\'t find any file.'));
		
		// Check if the file is allow
		$files = array();
		$file_list = array();
		foreach ( $raw_files as $folder => $folder_files ){
			foreach( $folder_files as $raw_file ){
				// Get the extension of the file
				if ( preg_match( '/^(.*)\.([^.]+)$/', $raw_file, $matches ) ){
					
					$filename = $matches[1];
					$ext = strtolower( $matches[2] );				
					
					// If extension is not in the acceptable list, skip it
					if ( in_array( $ext, $editable_extensions ) && apply_filters( $this->page_id.'filter_editable_file', true, $filename, $ext, $dir ) )
						$files[$folder.$raw_file] = $file_list[$folder][$raw_file] = array( 'name' => $raw_file, 'filename' => $filename, 'ext' => $ext, 'path' => $folder );					
				}
			}
		}
		
		// Get filename
		if( !empty( $_REQUEST['file'] ) && isset( $files[ $_REQUEST['file'] ] ) ){
			$current_file = $files[ $_REQUEST['file'] ];
		} else {
			$current_file = $files['/bootstrap.less'];
		}
		
		// Choose mode
		switch ( $current_file['ext'] ){
			case "js" : $mode = "text/javascript"; break;
			case "css": case 'less': $mode = "text/x-less"; break;
			case "php": default: $mode = "application/x-httpd-php";
		}
		
		// Get file full path
		$real_file = $dir.$current_file['path'].$current_file['name'];
		if ( ! is_file( $real_file ) ) {
			wp_die(sprintf('<p>%s</p>', __('No such file exists! Double check the name and try again.')));
		}

		$content = esc_textarea( file_get_contents( $real_file ) );
				
?>
			
			<div class="wrap">
				<h2 style="display:none"><?php echo esc_html( $title ); ?></h2>

				<div class="fileedit-sub">
					<div class="alignleft">
						<big>
							<?php 
							if ( is_writeable( $real_file ) )
								echo sprintf(__('Editing <strong>%s</strong>'), $current_file['name'] );
							else
								echo sprintf(__('Browsing <strong>%s</strong>'), $current_file['name'] );
							?>
						</big>
					</div>
					<form id="form-edit-css" class="ajax-save alignright" action="<?php echo add_query_arg( $_GET ) ?>" method="POST">
						<?php wp_nonce_field( 'omc_gen_bootstrap' ) ?>
						<input type="hidden" name="action" value="omc_gen_bootstrap" />
						<?php	submit_button( __( 'Gen Bootstrap' ), 'primary', 'submit', false );	?>
					</form>
					
					<br class="clear" />
				</div>

				<div id="templateside">					
					<ul>
					<li>
						<!-- ADD FILE FORM -->
						<form action="<?php echo add_query_arg( $_GET ) ?>" method="post">
							<label>Add new file</label>
							<input type="text" name="add_file" placeholder="New filename">
							<?php wp_nonce_field( 'omc_admin_add_new_file' ) ?>
							<input type="hidden" name="action" value="omc_admin_add_new_file" />
							<input type="hidden" name="dir" value="<?php echo $dir ?>" />
							<input type="hidden" name="ext" value="<?php echo esc_attr( maybe_serialize( $editable_extensions ) ) ?>" />
						</form>
					</li>
					<?php foreach ( $file_list as $folder => $folder_files ) :	?>
						<div class="folder-wrap">
							<h3><a><?php echo $folder == '/' ? 'General' : 'Mixin' ?></a></h3>
							<ul class="file-list">
							<?php foreach ( $folder_files as $file ) :	?>
							<li <?php echo $current_file['name'] == $file['name'] && $current_file['path'] == $file['path'] ? 'class="highlight"' : ''; ?>>
								<a href="<?php echo remove_query_arg( array( 'action_status' ), add_query_arg( array( 'file' => $file['path'].$file['name'] ) ) ) ?>"><?php echo $file['name'] ?></a>
							</li>
							<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
					</ul>
				</div>
			
			<form id="form-edit-css" class="ajax-save" action="<?php echo add_query_arg( $_GET ) ?>" method="POST">
			
				<?php wp_nonce_field( 'omc_edit_file' . $current_file['name'] ) ?>
				<input type="hidden" name="action" value="omc_edit_file" />				
				<input type="hidden" name="edit_file" value="<?php echo $current_file['name'] ?>" />
				<input type="hidden" name="edit_file_name" value="<?php echo $current_file['filename'] ?>" />
				<input type="hidden" name="edit_file_ext" value="<?php echo $current_file['ext'] ?>" />
				<input type="hidden" name="edit_file_path" value="<?php echo $dir.$current_file['path'] ?>" />
				<div>
					<textarea cols="70" rows="25" name="newcontent" class="code-editor" data-mode="<?php echo $current_file['ext'] ?>"  aria-describedby="newcontent-description"><?php echo $content; ?></textarea>
				</div>
			<?php if ( is_writeable( $real_file ) ) : ?>
				<p class="submit">
				<?php	submit_button( __( 'Save' ), 'primary', 'submit', false );	?>
				</p>
			<?php else : ?>
				<p><em><?php _e('You need to make this file writable before you can save your changes. See <a href="http://codex.wordpress.org/Changing_File_Permissions">the Codex</a> for more information.'); ?></em></p>
			<?php endif; ?>
			</form>
			
			<br class="clear" />
			
			</div>
			<?php
	}
}

new OMC_Bootstrap_Less_Editor_Settings;