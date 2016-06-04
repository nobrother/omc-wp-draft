<?php 

namespace OMC\Post\Admin;
use OMC\Post\Post;
use OMC\Post\Custom_Settings;
use OMC\Post_Object_Metabox;
use \WP_Exception as WP_Exception;

/*
 * Metabox
 */
class Metabox extends Post_Object_Metabox {
	
	/*
	 * Construct
	 */
	function __construct( $post_type = '', $object = '' ){
		parent::__construct( $post_type, $object );
		
		add_action( 'admin_init', array( $this, 'remove_post_support' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxs' ), 10, 2 );
		add_action( 'omc_admin_enqueue_scripts', array( $this, 'load_editor_scripts' ) );
	}
	
	/**
	 * Load editor javascripts
	 */
	public function load_editor_scripts(){
		
		global $post_type, $pagenow, $post;
		
		if( 'post' == $post_type && 'post.php' == $pagenow ){
		
			// Load editor js
			omc_add_theme_js( OMC_JS_THEME_DIR.'/admin-editor.js' );
			
			if( $post && 'publish' == $post->post_status ){
				$ctrl_s = array( 'target' => '#post');
				wp_localize_script( 'admin', 'ctrl_s_target', $ctrl_s );
			}
			
			// Load codemirror
			omc_load_codemirror();
		}
	}
	
	/*
	 * Add Editor meta box
	 */ 
	function add_metaboxs( $post_type, $post ) {
		
		$screen = get_current_screen();		
		if( $screen->action === 'add' )
			return false;
		
		if( $this->post_type !== $post_type )
			return false;
		
		// Add editor metabox
		if( $this->default_meta )
			add_meta_box( "omc_{$post_type}_editor_metabox", 'Editor', array( $this, 'editor_html' ), $post_type, 'normal', 'high' );
		
		// Load settings and meta
		parent::add_metaboxs( $post_type, $post );
	}
	
	/*
	 * Remove post editor
	 */
	function remove_post_support(){
		remove_post_type_support( 'post', 'editor' );
	}
	
	/*
	 * Metabox html
	 */
	function meta_html( $post ){
		
		wp_nonce_field( $this->metabox_meta_nonce_save_action, $this->metabox_meta_nonce_field );
		
		?>
		<table class="widefat fixed-layout table-layout-label-content">
			<tbody>
				<?php foreach( $this->default_meta as $meta_key => $default_value ): ?>		
				<tr>
					<th><strong><?php esc_html_e( $meta_key ) ?></strong></th>
					<td>
						<input type="text" 
							class="large-text" 
							name="<?php esc_attr_e( $this->html_name( $meta_key ) ) ?>" 
							value="<?php esc_attr_e( get_post_meta( $post->ID, $meta_key, true ) ) ?>" 
						/>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
		<?php	
	}
	
	/*
	 * Editor html
	 */
	function editor_html( $post ){		
		?>
		<textarea 
			class="wp-editor-area code-editor" 
			name="post_content" 
			id="post-content" data-mode="php"
		><?php echo $post->post_content; ?></textarea>
		
		<script>
			(function($){
				$(function(){
				var h = $(window).height() - 88;
					$('#post-content').height(h).data('height', h);
				})
			})(jQuery);
		</script>
		<?php
	}
}

new Metabox( 'post', new Post() );