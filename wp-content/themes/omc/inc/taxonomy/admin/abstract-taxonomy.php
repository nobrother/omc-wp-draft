<?php 
namespace OMC\Taxonomy\Admin;

abstract class Taxonomy{
	public $taxonomy;
	public $default_meta;
	
	/*
	 * Construct
	 */
	function __construct(){
		// Hook
		add_action( $this->taxonomy.'_add_form_fields', array( $this, 'add_form_fields' ) );
		add_action( $this->taxonomy.'_edit_form_fields', array( $this, 'edit_form_fields' ) );
		add_action( 'edit_'.$this->taxonomy, array( $this, 'save' ) );
		add_action( 'create_'.$this->taxonomy, array( $this, 'save' ) );
		add_filter( 'manage_edit-'.$this->taxonomy.'_columns', array( $this, 'add_columns' ) );
		add_filter( 'manage_'.$this->taxonomy.'_custom_column', array( $this, 'column_content' ), 10, 3 );
	}
	
	/*
	 * Html name
	 */
	function html_name( $meta_key = '' ){
		return $this->taxonomy.'_'.$meta_key;
	}	
	
	/*
	 * Add Form fields
	 */
	function add_form_fields(){ 
		foreach( $this->default_meta as $meta_key => $default_value ){
			$name = esc_attr( $this->html_name( $meta_key ) );
	?>
		<div class="form-field <?php echo $name ?>-wrap">
			<label for="<?php echo $name ?>"><?php _e( $meta_key ); ?></label>
			<input type="text" 
				name="<?php echo $name ?>" 
				id="<?php echo $name ?>" 
				class="<?php echo $name ?>"
				value="<?php esc_attr_e( $default_value ) ?>" />
		</div>
	<?php }
	}
	
	/*
	 * Edit Form fields
	 */
	function edit_form_fields( $term ){
		foreach( $this->default_meta as $meta_key => $default_value ){
			$name = esc_attr( $this->html_name( $meta_key ) );
			$value = get_term_meta( $term->term_id, $meta_key, true );
	?>
		<tr class="form-field <?php echo $name ?>-wrap">
			<th>
				<label for="<?php echo $name ?>"><?php _e( $meta_key ); ?></label>
			</th>
			
			<td>
				<input type="text" 
					name="<?php echo $name ?>" 
					id="<?php echo $name ?>" 
					class="<?php echo $name ?>"
					value="<?php esc_attr_e( $value ) ?>" />
			</td>
		</tr>
	<?php }
	}
	
	/*
	 * Save
	 */
	function save( $term_id ){
		foreach( $this->default_meta as $meta_key => $default_value ){
			$name = $this->html_name( $meta_key );
			if( isset( $_POST[$name] ) ){
				update_term_meta( $term_id, $meta_key, $_POST[$name] );
			}
		}
	}
	
	/*
	 * Add Column
	 */
	function add_columns( $columns ) {
		foreach( $this->default_meta as $meta_key => $default_value ){
			$columns[$meta_key] = $meta_key;
		}		
		return $columns;
	}
	
	/*
	 * Output column content
	 */
	function column_content( $out, $column, $term_id ){
		if( array_key_exists( $column, $this->default_meta ) ){			
			return get_term_meta( $term_id, $column, true );			
		}
		return $out;
	}
}