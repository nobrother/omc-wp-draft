<?php

namespace OMC;

/*
 * Post Object Metabox Class
 */
abstract class Post_Object_Metabox {
	
	// Should define in child class
	public $post_type;
	public $object;
	public $metabox_settings_title = 'Settings';
	public $metabox_meta_title = 'Variables';
	public $metabox_settings_nonce_field = 'metabox_settings_nonce';
	public $metabox_meta_nonce_field = 'metabox_meta_nonce';
	public $metabox_settings_save_action = 'metabox_settings_save_action';
	public $metabox_meta_nonce_save_action = 'metabox_meta_nonce_save_action';
	public $meta_key_html_name_prefix = 'meta_key_';
	public $custom_settings;
	public $default_meta;
	 
	/*
	 * Construct
	 */
	function __construct( $post_type = '', $object = '' ){
		// Set variable: Post type
		if( !empty( $post_type ) )
			$this->post_type = $post_type ;
		
		// Set variable: Object
		if( is_object( $object ) )
			$this->object = $object;
		
		// Set variable: Custom settings object
		if( !empty( $this->object->custom_settings ) )
			$this->custom_settings = $this->object->custom_settings;
		
		// Set variable: Default meta
		if( method_exists( $this->object, 'get_default_meta' ) )
			$this->default_meta = $this->object->get_default_meta();
		
		// Set variable: Meta key html prefix
		$this->meta_key_html_name_prefix = $this->post_type.'_meta_key_';		
		
		// Hook
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxs' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );		
	}
	
	/*
	 * Add meta box
	 */ 
	function add_metaboxs( $post_type, $post ) {
		
		$screen = get_current_screen();		
		if( $screen->action === 'add' )
			return false;
		
		if( $this->post_type !== $post_type )
			return false;
		
		// Add post meta metabox
		if( $this->default_meta )
			add_meta_box( "omc_{$post_type}_meta_metabox", $this->metabox_meta_title, array( $this, 'meta_html' ), $post_type, 'normal', 'high' );
		
		// Add settings metabox
		if( $this->custom_settings )
			add_meta_box( "omc_{$post_type}_settings_metabox", $this->metabox_settings_title, array( $this, 'settings_html' ), $post_type, 'normal', 'high' );
	}

	// Metabox settings html
	function settings_html( $post ){ echo 'You forget to overide the settings html'; }
	
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
	
	/**
	 * Save settings
	 */
	function save( $post_id, $post ){
		
		// Post type check
		if( $post->post_type != $this->post_type )
			return;
		
		// Status check
		if( $post->post_status == 'auto-draft' )
			return;
		
		// First time save
		if( $post->post_date == $post->post_modified )
			$this->first_time_save( $post_id, $post );
		
		// Subsequence save
		$this->subsequence_save( $post_id, $post );		
	}
	
	/**
	 * First time save
	 */
	function first_time_save( $post_id, $post ){ return; }
	
	/**
	 * Subsequnce save
	 */
	function subsequence_save( $post_id, $post ){
		
		// Save settings		
		if( 
				isset( $_POST[$this->metabox_settings_nonce_field] ) 
				&& wp_verify_nonce( $_POST[$this->metabox_settings_nonce_field], $this->metabox_settings_save_action )
				&& ( $settings = $this->custom_settings )
				&& isset( $_POST[$settings->key] )
				&& is_array( $_POST[$settings->key] )
		){
			$settings->set( $_POST[$settings->key], $post );
		}
		
		// Save meta
		if( 
				isset( $_POST[$this->metabox_meta_nonce_field] ) 
				&& wp_verify_nonce( $_POST[$this->metabox_meta_nonce_field], $this->metabox_meta_nonce_save_action )
				&& ( $object = $this->object )
				&& ( $default_meta = $object->get_default_meta() )
				&& is_array( $default_meta )
		){
			foreach( $default_meta as $meta_key => $default_value ){
				if( isset( $_POST[$this->html_name( $meta_key )] ) ){
					if( $value = $_POST[$this->html_name( $meta_key )] )
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
	function html_name( $meta_key ){
		return $this->meta_key_html_name_prefix.$meta_key;
	}
}