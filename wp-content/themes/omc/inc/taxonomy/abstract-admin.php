<?php 
namespace OMC\Taxonomy;

abstract class Abstract_Admin{
	
	protected static $taxonomy;
	protected static $object_classname;
	protected static $default_meta;
	
	/*
	 * Initialize
	 */
	static function init(){
		
		$classname = get_called_class();
		$object_classname = static::$object_classname;
		static::$default_meta = $object_classname::$default_meta;
		
		// Hook
		if( !empty( static::$default_meta ) ){
			add_action( static::$taxonomy.'_add_form_fields', array( $classname, 'add_form_fields' ) );
			add_action( static::$taxonomy.'_edit_form_fields', array( $classname, 'edit_form_fields' ) );
			add_action( 'edit_'.static::$taxonomy, array( $classname, 'save' ) );
			add_action( 'create_'.static::$taxonomy, array( $classname, 'save' ) );
			add_filter( 'manage_edit-'.static::$taxonomy.'_columns', array( $classname, 'add_columns' ) );
			add_filter( 'manage_'.static::$taxonomy.'_custom_column', array( $classname, 'column_content' ), 10, 3 );
		}
		// Add a "Go back" link to the info ctg edit form.
		add_action( static::$taxonomy.'_edit_form', array( $classname, 'add_go_back_btn' ) );
			
			// Stay on the edit term page after taxonomy term updating.
		add_filter( 'wp_redirect', array( $classname, 'redirect_back_to_edit_term_page_after_save' ) );
	}
	
	/*
	 * Html name
	 */
	static function html_name( $meta_key = '' ){
		return static::$taxonomy.'_'.$meta_key;
	}	
	
	/*
	 * Add Form fields
	 */
	static function add_form_fields(){ 
		foreach( static::$default_meta as $meta_key => $default_value ){
			$name = esc_attr( static::html_name( $meta_key ) );
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
	static function edit_form_fields( $term ){
		
		foreach( static::$default_meta as $meta_key => $default_value ){
			$name = esc_attr( static::html_name( $meta_key ) );
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
	static function save( $term_id ){
		foreach( static::$default_meta as $meta_key => $default_value ){
			$name = static::html_name( $meta_key );
			if( isset( $_POST[$name] ) ){
				update_term_meta( $term_id, $meta_key, $_POST[$name] );
			}
		}
	}
	
	/**
	 * Redirect back to edit term page after save
	 */
	static function redirect_back_to_edit_term_page_after_save( $location ){
		
		$args = array(
			'action'   => FILTER_SANITIZE_STRING,
			'taxonomy' => FILTER_SANITIZE_STRING,
			'tag_ID'   => FILTER_SANITIZE_NUMBER_INT,
		);
		$_inputs    = filter_input_array( INPUT_POST, $args );
		$_post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
		if( 'editedtag' === $_inputs['action'] 
			&& static::$taxonomy === $_inputs['taxonomy']
			&& $_inputs['tag_ID'] > 0
		){
			$location = add_query_arg( 'action',   'edit',               $location );
			$location = add_query_arg( 'taxonomy', $_inputs['taxonomy'], $location );
			$location = add_query_arg( 'tag_ID',   $_inputs['tag_ID'],   $location );
			if( $_post_type )
					$location = add_query_arg( 'post_type', $_post_type, $location );
		}
		return $location;
	}
	
	// Add a "Go back" link to the info ctg edit form.
	static function add_go_back_btn( $tag ) {
		$url = admin_url( 'edit-tags.php' );
		$url = add_query_arg( 'tag_ID',   $tag->term_id, $url );
		$url = add_query_arg( 'taxonomy', $tag->taxonomy, $url );
		printf( '<a href="%s">%s</a>', $url, __( 'Go back' ) );
	}
	
	/*
	 * Add Column
	 */
	static function add_columns( $columns ) {
		foreach( static::$default_meta as $meta_key => $default_value ){
			$columns[$meta_key] = $meta_key;
		}		
		return $columns;
	}
	
	/*
	 * Output column content
	 */
	static function column_content( $out, $column, $term_id ){
		if( array_key_exists( $column, static::$default_meta ) ){			
			return get_term_meta( $term_id, $column, true );			
		}
		return $out;
	}
}