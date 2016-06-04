<?php
/**
 * omc custom taxonomy
 * Create custom taxanomy
 */
class OMC_Taxonomy {
	
	/**
	 * Post type 
	 */
	protected $taxonomy = null;
	
	/**
	 * Indicate taxonomy is registered
	 */
	protected $is_registered = false;
	
	/**
	 * Taxonomy object type
	 */
	protected $object_type = array();
	
	/**
	 * Taxonomy argument
	 */
	protected $args = array();
	
	/**
	 * Taxonomy Labels
	 */
	protected $labels = array();
	
	/**
	 * Taxonomy Single Label
	 */
	protected $single_label = null;
	
	/**
	 * Taxonomy Prural Label
	 */
	protected $prural_label = null;
	
	
	/**
	 * Contruct
	 * Register Taxonomy in init hook
	 */
	function __construct( $taxonomy, $object_type, $single_label = '', $prural_label = '', $labels = array(), $args = array() ) {
		
		// Set variable
		$this->taxonomy 			= $taxonomy;
		$this->object_type 		= $object_type;
		$this->single_label 	= !empty( $single_label ) ? $single_label : $taxonomy;
		$this->prural_label 	= !empty( $prural_label ) ? $prural_label : $taxonomy;
		
		/**
		 * Construct labels
		 * %1$s = single label
		 * %2$s = prural label
		 */
		$this->labels = wp_parse_args( $labels, array(
			'name'               => '%2$s',
			'singular_name'      => '%1$s',
			'menu_name'          => '%2$s',
			'all_items'          => 'All %2$s',
			'edit_item'          => 'Edit %1$s',
			'view_item'          => 'View %1$s',
			'update_item'        => 'Update %1$s',
			'add_new_item'       => 'Add New %1$s',
			'new_item_name'      => 'New %1$s',
			'parent_item'  			 => 'Parent %2$s',
			'parent_item_colon'  => 'Parent %2$s:',
			'search_items'       => 'Search %2$s',
			'popular_items'      => 'Popular %2$s',
			'separate_items_with_commas' => 'Separate %2$s with commas',
			'add_or_remove_items'	=> 'Add or remove %2$s',
			'choose_from_most_used'	=> 'Choose from the most used %2$s',
			'not_found'          => 'No %1$s found.',
		) );
		
		/**
		 * Construct arguments
		 * %1$s = single label
		 * %2$s = prural label
		 */
		$this->args = wp_parse_args( $args, array( 
			'public' => true,	//  If the taxonomy should be publicly queryable.
			'show_ui' => true,	// Whether to generate a default UI for managing this taxonomy.
			'show_in_nav_menus' => true,	// true makes this taxonomy available for selection in navigation menus.
			'show_tagcloud' => true, // Whether to allow the Tag Cloud widget to use this taxonomy.
			'show_in_quick_edit' => true, // Whether to show the taxonomy in the quick/bulk edit panel. (Available since 4.2)
			/**
			 * Provide a callback function name for the meta box display. (Available since 3.8)
			 * Defaults to the categories meta box (post_categories_meta_box() in meta-boxes.php) for hierarchical taxonomies 
			 * and the tags meta box (post_tags_meta_box()) for non-hierarchical taxonomies. 
			 * No meta box is shown if set to false.
			 */
			'meta_box_cb' => null,
			'show_admin_column' => true, // Whether to allow automatic creation of taxonomy columns on associated post-types table. (Available since 3.5)
			/**
			 * Is this taxonomy hierarchical (have descendants) like categories or not hierarchical like tags.
			 * Hierarchical taxonomies will have a list with checkboxes to select an existing category 
			 * in the taxonomy admin box on the post edit page (like default post categories). 
			 * Non-hierarchical taxonomies will just have an empty text field to type-in taxonomy terms 
			 * to associate with the post (like default post tags).
			 */
			'hierarchical' => false, 
			'update_count_callback' => '', // TODO: Too complecated, go see documentation urself
			'query_var' => true, // False to disable the query_var, set as string to use custom query_var instead of default which is $taxonomy, the taxonomy's "name".
			/**
			 * Set to false to prevent automatic URL rewriting a.k.a. "pretty permalinks". Pass an $args array to override default URL settings for permalinks as outlined below:
			 * 	'slug' => Used as pretty permalink text (i.e. /tag/) - defaults to $taxonomy (taxonomy's name slug)
			 * 	'with_front' => allowing permalinks to be prepended with front base - defaults to true
			 *	'hierarchical' => true or false allow hierarchical urls (implemented in Version 3.1) - defaults to false
			 *	'ep_mask' => (Required for pretty permalinks) Assign an endpoint mask for this taxonomy 
			 *								- defaults to EP_NONE. If you do not specify the EP_MASK, pretty permalinks will not work.
			 */
			'rewrite' => array(
				'slug' => $this->taxonomy,
				'with_front' => false,
				'hierarchical' => false,
				'ep_mask' => EP_NONE
			),
			
			
		) );
		
		return $this;
	}
	
	/**
	 * Set taxonomy property value
	 */
	protected function set( $property_name, $value ){
		
		// Cannot set property value if already register
		if( $this->is_registered )
			return $this;
			
		// Can set allowed properties only
		if( isset( $this->$property_name ) )
			$this->$property_name = $value;
			
		return $this;
	}
	
	/**
	 * Set object type
	 */
	function set_object_type( $value ){
		return $this->set( 'object_type', $value );
	}
	
	/**
	 * Set Single label
	 */
	function set_single_label( $value ){
		return $this->set( 'single_label', $value );
	}
	
	/**
	 * Set Prural label
	 */
	function set_prural_label( $value ){
		return $this->set( 'prural_label', $value );
	}
	
	/**
	 * Set labels
	 */
	function set_labels( $name, $value = '' ){
		if( !empty( $name ) && is_string( $name ) )
			$labels = array( $name => $value );
		elseif( !empty( $name ) && is_array( $name ) )
			$labels = $name;
		else
			return $this;
			
		return $this->set( 'labels', array_replace_recursive( $this->labels, $labels ) );
	}
	
	/**
	 * Set Arguments
	 */
	function set_args( $name, $value = '' ){
		if( !empty( $name ) && is_string( $name ) )
			$args = array( $name => $value );
		elseif( !empty( $name ) && is_array( $name ) )
			$args = $name;
		else
			return $this;
			
		return $this->set( 'args', array_replace_recursive( $this->args, $args ) );
	}
	
	
	/**
	 * Register taxonomy
	 */
	function register(){
		
		// Register taxonomy if not register yet
		if( !$this->is_registered ){
			
			// Add labels to arguments
			$single_label = $this->single_label;
			$prural_label = $this->prural_label;
			$label_fn = function( $tmp ) use ( $single_label, $prural_label ){
				return sprintf( $tmp, $single_label, $prural_label );
			};
			$this->args['labels'] = array_map( $label_fn, $this->labels );			
			
			// Register taxonomy in init hook
			add_action( 'init', function( $this ){
				register_taxonomy( $this->taxonomy, $this->object_type, $this->args );
			} );
			
			// Add extra options taxonomy
			add_action( $this->taxonomy.'_edit_form_fields', array( $this, 'taxonomy_extra_options_html' ) );	
			add_action( 'edited_'.$this->taxonomy, array( $this, 'save_taxonomy_extra_options' ) );  
			
			// Add a "Go back" link to the info ctg edit form.
			add_action( $this->taxonomy.'_edit_form', array( $this, 'add_go_back_btn' ) );
			
			// Stay on the edit term page after taxonomy term updating.
			add_filter( 'wp_redirect', array( $this, 'redirect_back_to_edit_term_page_after_save' ) );
			
			// taxonomy registered
			$this->is_registered = true;
		}
		 
		return $this;
	}
	
	/**
	 * Extra Options html
	 */
	function taxonomy_extra_options_html( $term ){
		$option_key = 'taxonomy_term_'.$term->term_id.'_extra_options';
		$options = get_option( $option_key );
		
		// Do before default extra options
		do_action( $this->taxonomy.'_before_default_extra_options', $option_key, $term );
		
		// If default extra options is allowed
		if( apply_filters( 'is_load_'.$this->taxonomy.'_default_extra_options', true, $option_key, $term ) ): ?> 
		
		<tr class="form-field">  
			<th scope="row" valign="top">
				<label><?php _e( 'Label' ); ?></label>
			</th>  
			<td>  
				<textarea name="<?php esc_attr_e( $option_key ) ?>[label]" rows="5" cols="50" class="large-text"><?php isset( $options['label'] ) && _e( esc_textarea( $options['label'] ) ) ?></textarea>
				<p class="description">You can use html tag here</p> 
			</td>  
		</tr>  
		
		<?php endif;
		
		// Do after default extra options
		do_action( $this->taxonomy.'_after_default_extra_options', $option_key, $term );
	}
	
	/**
	 * Save Extra Options
	 */
	function save_taxonomy_extra_options( $term_id ){
	
		$option_key = 'taxonomy_term_'.$term_id.'_extra_options';
		
		if ( isset( $_POST[$option_key] ) ) {  
			
			$input = $_POST[$option_key];
			
			$options = get_option( $option_key );  
			if( !empty( $options ) )
				$options = array_replace_recursive( $options, $input );
			else
				$options = $input;
			
			update_option( $option_key, $options );
			
		}
	}
	
	/**
	 * Redirect back to edit term page after save
	 */
	function redirect_back_to_edit_term_page_after_save( $location ){
		$mytaxonomy = $this->taxonomy; # <-- Edit this to your needs!
		$args = array(
			'action'   => FILTER_SANITIZE_STRING,
			'taxonomy' => FILTER_SANITIZE_STRING,
			'tag_ID'   => FILTER_SANITIZE_NUMBER_INT,
		);
		$_inputs    = filter_input_array( INPUT_POST, $args );
		$_post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
		if( 'editedtag' === $_inputs['action'] 
			&& $mytaxonomy === $_inputs['taxonomy']
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
	function add_go_back_btn( $tag ) {
		$url = admin_url( 'edit-tags.php' );
		$url = add_query_arg( 'tag_ID',   $tag->term_id, $url );
		$url = add_query_arg( 'taxonomy', $tag->taxonomy, $url );
		printf( '<a href="%s">%s</a>', $url, __( 'Go back' ) );
	}
}

/**
 * Function to manage omc cpt object
 * Please use this function instead of direct access
 */
function omc_taxonomy( $taxonomy, $object_type, $single_label = '', $prural_label = '', $labels = array(), $args = array() ){
	
	global $omc_taxonomy_obj;
	
	// Create object if never created before
	if( !isset( $omc_taxonomy_obj[$taxonomy] ) )
		$omc_taxonomy_obj[$taxonomy] = new OMC_Taxonomy( $taxonomy, $object_type, $single_label, $prural_label, $labels, $args );
		
	return $omc_taxonomy_obj[$taxonomy];
}

do_action( 'omc_taxonomy_init' );
