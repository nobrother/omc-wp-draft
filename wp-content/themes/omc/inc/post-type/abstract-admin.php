<?php
namespace OMC\Post_Type;
use \WP_Exception as WP_Exception;

/*
 * Post Object Metabox Class
 */
abstract class Abstract_Admin {
	
	// Should define in child class
	protected static $post_type;
	protected static $object_class = '';
	protected static $default_meta;
	
	protected static $metabox_meta_title = 'Variables';
	protected static $metabox_meta_nonce_field = 'metabox_meta_nonce';
	protected static $metabox_meta_nonce_save_action = 'metabox_meta_nonce_save_action';
	protected static $meta_key_html_name_prefix = 'meta_key_';
	
	static function debug(){
		var_dump( static::$post_type );
		var_dump( get_called_class() );
	}
	
	/*
	 * Construct
	 */
	static function init( $post_type = '', $object_class = '' ){
		
		$post_type = static::$post_type;
		$object_class = static::$object_class;
		
		// Set variable: Default meta
		static::$default_meta = $object_class::$default_meta;	
		
		// Set variable: Meta key html prefix
		static::$meta_key_html_name_prefix = static::$post_type.'_meta_key_';		
		
		// Hook
		$classname = get_called_class();
		add_action( 'add_meta_boxes', array( $classname, 'add_metaboxs' ), 10, 2 );
		add_action( 'save_post', array( $classname, 'save_post' ), 10, 2 );		
	}
	
	/*
	 * Add meta box
	 */ 
	static function add_metaboxs( $post_type, $post ) {
		
		$screen = get_current_screen();		
		if( $screen->action === 'add' )
			return false;
		
		if( static::$post_type !== $post_type )
			return false;
		
		// Add post meta metabox
		if( static::$default_meta )
			add_meta_box( "omc_{$post_type}_meta_metabox", static::$metabox_meta_title, array( get_called_class(), 'meta_html' ), $post_type, 'normal', 'high' );
		
		// Add extra metabox
		if( method_exists( get_called_class(), 'add_extra_metaboxs' ) )
			static::add_extra_metaboxs( $post_type, $post );
	}
	
	/*
	 * Metabox html
	 */
	static function meta_html( $post ){
		wp_nonce_field( static::$metabox_meta_nonce_save_action, static::$metabox_meta_nonce_field );
		?>
		<table class="widefat fixed-layout table-layout-label-content">
			<tbody>
				<?php foreach( static::$default_meta as $meta_key => $default_value ): ?>		
				<tr>
					<th><strong><?php esc_html_e( $meta_key ) ?></strong></th>
					<td>
						<input type="text" 
							class="large-text" 
							name="<?php esc_attr_e( static::html_name( $meta_key ) ) ?>" 
							value="<?php esc_attr_e( get_post_meta( $post->ID, $meta_key, true ) ) ?>" 
						/>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
		<?php	
	}
	/**
	 * Save settings
	 */
	static function save_post( $post_id, $post ){
		// Post type check
		if( $post->post_type != static::$post_type )
			return;
		
		// Status check
		if( $post->post_status == 'auto-draft' )
			return;
		
		// First time save
		if( $post->post_date == $post->post_modified )
			static::first_time_save( $post_id, $post );
		
		// Subsequence save
		static::save( $post_id, $post );		
	}
	/**
	 * First time save
	 */
	static function first_time_save( $post_id, $post ){
		// Save default meta
		foreach( static::$default_meta as $meta_key => $default_value )
			update_post_meta( $post_id, $meta_key, $default_value );
	}
	/**
	 * Subsequnce save
	 */
	static function save( $post_id, $post ){
		// Save meta
		if( 
				isset( $_POST[static::$metabox_meta_nonce_field] ) 
				&& wp_verify_nonce( $_POST[static::$metabox_meta_nonce_field], static::$metabox_meta_nonce_save_action )
		){

			foreach( static::$default_meta as $meta_key => $default_value ){
				if( isset( $_POST[static::html_name( $meta_key )] ) ){
					if( $value = $_POST[static::html_name( $meta_key )] )
						update_post_meta( $post_id, $meta_key, $value );
					else
						update_post_meta( $post_id, $meta_key, $default_value );
				}
			}
		}
		//die();
	}
	/**
	 * Helper function: Generate html name of a meta key
	 */
	static function html_name( $meta_key ){
		return static::$meta_key_html_name_prefix.$meta_key;
	}
}