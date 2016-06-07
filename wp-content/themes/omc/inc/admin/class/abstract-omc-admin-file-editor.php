<?php
/**
 * OMC CSS Editor
 * Registers a admin theme settings/options page, providing content and corresponding menu item for it
 */

abstract class OMC_File_Editor_Settings extends OMC_Admin_Basic {

	/**
	 * Create an admin menu item and settings page.
	 */
	public function __construct() {
		
		// Load action for save, add, and delete files
		$this->add_actions();
		
	}
	
	/**
	 * Load action for save, add, and delete files
	 */
	public function add_actions(){
		// Call this action to save
		add_action( 'admin_init', array( $this, 'save_file' ) );
		
		// Call this action to add
		add_action( 'admin_init', array( $this, 'add_file' ) );
		
		// Call this action to delete
		add_action( 'admin_init', array( $this, 'del_file' ) );
		
		// Call this action to embed script
		add_action( $this->page_id.'_file_editor_html', array( $this, 'admin_embed_extra_js' ), 20 );
		
		// Enqueue code mirror scripts
		add_action( 'omc_admin_enqueue_scripts', array( $this, 'load_editor_scripts' ) );
	}
	
	/**
	 * Load editor javascripts
	 */
	public function load_editor_scripts(){
		if( is_menu_page( $this->page_id ) ){
		
			// Load editor js
			wp_localize_script( 'admin', 'ctrl_s_target', array( 'target' => 'form.ajax-save'	) );
			
			// Load codemirror
			omc_load_codemirror();
			
			// Load phpFileTree
			wp_enqueue_style( 'phpFileTree', OMC_INC_URL.'/apps/phpFileTree/styles/default/default.css', array(), false, 'all' );
			wp_enqueue_script( 'phpFileTree', OMC_INC_URL.'/apps/phpFileTree/php_file_tree_jquery.js', array(), false, true );
		}
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
					) 
			)
			return;

		// Get current file		
		$newcontent = wp_unslash( $_POST['newcontent'] );		
		$file = wp_unslash( $_POST['edit_file'] );
		
		if ( is_writeable( $file ) ) {
			
			// Write new content to file
			file_put_contents( $file, $newcontent );
			
			// Sets access and modification time of file
			touch( $file );
			
			do_action( 'omc_admin_file_editor_after_file_saved', omc_pathinfo( $file ) );
			
		}
		
		die(); // because it is and ajax request
	}
	
	/**
	 * Add new file
	 */
	public function add_file(){
		
		// Check on the right page		
		if( ! is_menu_page( $this->page_id ) )
			return;
		if( ! ( 
						isset( $_POST['action'], $_POST['add_file'], $_POST['_wpnonce'] ) 
						&& $_POST['action'] == 'omc_admin_add_new_file'
					) 
			)
			return;
		
		// Security check
		if( !wp_verify_nonce( $_POST['_wpnonce'], 'omc_admin_add_new_file' ) )
			wp_safe_redirect( add_query_arg( array( 'action_status' => 'nononce' ) ) );
		
		$basename = wp_unslash( $_POST['add_file'] );
		$ext = pathinfo( $basename, PATHINFO_EXTENSION );
		$dir = wp_unslash( $_POST['dir'] );
		$file = "$dir/$basename";
		$editable_extensions = maybe_unserialize( stripslashes( $_POST['ext'] ) );
		
		// Check extension
		if( !empty( $editable_extensions ) && is_array( $editable_extensions ) ){
			if( !in_array( $ext, $editable_extensions ) )
				wp_safe_redirect( add_query_arg( array( 'action_status' => 'invalid' ) ) );	
		}
		
		// Check if file exists
		if( file_exists( $file ) )
			wp_safe_redirect( add_query_arg( array( 'action_status' => 'duplicate' ) ) );
		
		
		
		// Create file
		file_put_contents( $file, '' );
		
		wp_safe_redirect( add_query_arg( array( 'file' => $file, 'action_status' => 'success' ) ) );
	}
	
	/**
	 * Delete file
	 */
	public function del_file(){
	
	}
	
	/**
	 * Display notices on the save or reset of settings
	 */
	public function notices() {

		if ( ! is_menu_page( $this->page_id ) )
			return;
		
		if ( isset( $_REQUEST['action_status'] ) ){
		
			echo '<div id="message" class="updated"><p><strong>';
			
			switch( $_REQUEST['action_status'] ){
				case 'nononce': echo 'No cheating please!'; break;
				case 'invalid': echo 'Invalid filename'; break;
				case 'duplicate': echo 'The file is already exists'; break;
				case 'success': echo 'New file created'; break;
			}
			
			echo '</strong></p></div>';
			
			unset( $_GET['action_status'] );
		}

	}
	
	/**
	 * Callback for displaying the admin page.
	 */
	public function admin(){
		do_action( $this->page_id.'_file_editor_html' );
	}
	
	/**
	 * Embed extra js
	 */
	public function admin_embed_extra_js(){ ?>
		<script>
			(function($){
				$(function(){
				var h = $(window).height() - 88;
					$('.code-editor').height(h).data('height', h);
				})
			})(jQuery);
		</script>
	<?php }
	
	/**
	 * Editor html
	 */
	public function print_editor( $title = "Edit file", $editable_extensions = array(), $dir ){
		
		// Check dir exists
		if( !is_dir( $dir ) )
			wp_die(sprintf('<p>%s</p>', 'Cant\'t find any file.h'));
		
		/*
		 * Check file exists
		 * $_REQUEST['file'] will hold the full path of the file
		*/
		$current_file = '';
		if( !empty( $_REQUEST['file'] ) && file_exists( $_REQUEST['file'] ) )
			$current_file = wp_unslash( $_REQUEST['file'] );
		
		require_once( OMC_APPS_DIR.'/phpFileTree/php_file_tree.php' );
		$file_tree_html = php_file_tree( $dir, add_query_arg( 'file', '[link]' ), $editable_extensions, $current_file );
		
		// Check file exists
		if( !file_exists( $current_file ) )
			wp_die(sprintf('<p>%s</p>', 'Cant\'t find any file.a'));
		
		$current_file_info = pathinfo( $current_file );
		
		// Choose mode
		switch ( $current_file_info['extension'] ){
			case "js" : 
				$mode = "text/javascript"; 
				break;
			case "css": case 'less': 
				$mode = "text/x-less"; 
				break;
			case "php": default: 
				$mode = "application/x-httpd-php";
		}
		
		// Get current file content
		$content = esc_textarea( file_get_contents( $current_file ) );

		// GET
		$_GET = array_map( 'stripslashes_deep', $_GET );
?>
			
			<div class="wrap">
				<h2 style="display:none"><?php echo esc_html( $title ); ?></h2>

				<div class="fileedit-sub">
					<div class="alignleft">
						<big>
							<?php 
							if ( is_writeable( $current_file ) )
								echo sprintf(__('Editing <strong>%s</strong>'), $current_file );
							else
								echo sprintf(__('Browsing <strong>%s</strong>'), $current_file );
							?>
						</big>
					</div>
					<br class="clear" />
				</div>

				<div id="templateside">
					
					<ul>
						<li>
							<!-- ADD FILE FORM -->
							<form id="form-omc-editor-add-file" action="<?php echo add_query_arg( $_GET ) ?>" method="post">
								<label>Add new file</label>
								<input type="text" name="add_file" placeholder="New filename">
								<?php wp_nonce_field( 'omc_admin_add_new_file' ) ?>
								<input type="hidden" name="action" value="omc_admin_add_new_file" />
								<input type="hidden" name="dir" value="<?php esc_attr_e( $current_file_info['dirname'] ) ?>" />
								<input type="hidden" name="ext" value="<?php esc_attr_e( maybe_serialize( $editable_extensions ) ) ?>" />
							</form>
						</li>
						<li>
							<?php echo $file_tree_html ?>
						</li>
					</ul>
				</div>
			
			<form id="form-edit-css" class="ajax-save" action="<?php echo add_query_arg( $_GET ) ?>" method="POST">
			
				<?php wp_nonce_field( 'omc_edit_file' . $current_file ) ?>
				<input type="hidden" name="action" value="omc_edit_file" />				
				<input type="hidden" name="edit_file" value="<?php echo esc_attr( $current_file ) ?>" />
					
				<div>
					<textarea cols="70" rows="25" name="newcontent" class="code-editor" 
						data-mode="<?php esc_attr_e( $current_file_info['extension'] ) ?>" 
						data-editor-id=<?php !empty( $this->page_id ) && esc_attr_e( 'editor-'.$this->page_id ) ?> aria-describedby="newcontent-description"
					><?php echo $content; ?></textarea>
				</div>
			<?php if ( is_writeable( $current_file ) ) : ?>
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