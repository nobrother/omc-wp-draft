<?php
/**
 * omc custom post type
 * Create custom post type and custom taxanomy
 */
class OMC_CPT {
	
	/**
	 * Post type 
	 */
	protected $post_type = null;
	
	/**
	 * Indicate CPT is registered
	 */
	protected $is_registered = false;
	
	/**
	 * CPT argument
	 */
	protected $args = array();
	
	/**
	 * CPT Labels
	 */
	protected $labels = array();
	
	/**
	 * CPT Single Label
	 */
	protected $single_label = null;
	
	/**
	 * CPT Prural Label
	 */
	protected $prural_label = null;
	
	
	/**
	 * Contruct
	 * Register CPT in init hook
	 */
	function __construct( $post_type, $single_label = '', $prural_label = '', $labels = array(), $args = array() ) {
		
		// Set variable
		$this->post_type 			= $post_type;
		$this->single_label 	= !empty( $single_label ) ? $single_label : $post_type;
		$this->prural_label 	= !empty( $prural_label ) ? $prural_label : $post_type;
		
		/**
		 * Construct labels
		 * %1$s = single label
		 * %2$s = prural label
		 */
		$this->labels = wp_parse_args( $labels, array(
			'name'               => '%2$s',
			'singular_name'      => '%1$s',
			'menu_name'          => '%2$s',
			'name_admin_bar'     => '%1$s',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New %1$s',
			'new_item'           => 'New %1$s',
			'edit_item'          => 'Edit %1$s',
			'view_item'          => 'View %1$s',
			'all_items'          => 'All %2$s',
			'search_items'       => 'Search %2$s',
			'parent_item_colon'  => 'Parent %2$s:',
			'not_found'          => 'No %1$s found.',
			'not_found_in_trash' => 'No %1$s found in Trash.',
		) );
		
		/**
		 * Construct arguments
		 * %1$s = single label
		 * %2$s = prural label
		 */
		$this->args = wp_parse_args( $args, array( 
			'description' => '', 	// A short descriptive summary of what the post type is.
			'public' => true,	// Controls how the type is visible to authors (show_in_nav_menus, show_ui) and readers (exclude_from_search, publicly_queryable)
			'exclude_from_search' => false, // Whether to exclude posts with this post type from front end search results.
			'publicly_queryable' => true, // Whether queries can be performed on the front end as part of parse_request().
			'show_ui' => true, // Whether to generate a default UI for managing this post type in the admin.
			'show_in_nav_menus' => true, // Whether post_type is available for selection in navigation menus.
			'show_in_menu' => true, // Where to show the post type in the admin menu. show_ui must be true.
			'show_in_admin_bar' => true, // Whether to make this post type available in the WordPress admin bar.
			/**
			 * The position in the menu order the post type should appear. show_in_menu must be true.
			 * 	5 - below Posts
			 *	10 - below Media
			 *	15 - below Links
			 *	20 - below Pages
			 *	25 - below comments
			 *	60 - below first separator
			 *	65 - below Plugins
			 *	70 - below Users
			 *	75 - below Tools
			 *	80 - below Settings
			 *	100 - below second separator
			 */
			'menu_position' => 10,
			/**
			 * The url to the icon to be used for this menu or the name of the icon from the iconfont
			 * Examples:
			 * 'dashicons-video-alt' (Uses the video icon from Dashicons)
			 * 'get_template_directory_uri() . "images/cutom-posttype-icon.png"' (Use a image located in the current theme)
			 */
			'menu_icon' => 'dashicons-index-card',
			//'capability_type' => 'post', // TODO: Not understand of this
			/**
			 * Whether the post type is hierarchical (e.g. page). 
			 * Allows Parent to be specified. The 'supports' parameter should contain 
			 * 'page-attributes' to show the parent select box on the editor page.
			 */
			'hierarchical' => true,
			
			/**
			 * Set post_type for the parent 
			 * Allows Parent post type to be different
			 */ 
			'parent_post_type' => $post_type,
			
			/**
			 * An alias for calling add_post_type_support() directly. 
			 * As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behavior.
			 *	'title'
			 *	'editor' (content)
			 *	'author'
			 *	'thumbnail' (featured image, current theme must also support post-thumbnails)
			 *	'excerpt'
			 *	'trackbacks'
			 *	'custom-fields'
			 *	'comments' (also will see comment count balloon on edit screen)
			 *	'revisions' (will store revisions)
			 *	'page-attributes' (menu order, hierarchical must be true to show Parent option)
			 *	'post-formats' add post formats, see Post Formats
			 */
			'supports' => array(),
			/**
			 * An array of registered taxonomies like category or post_tag that will be used with this post type. 
			 * This can be used in lieu of calling register_taxonomy_for_object_type() directly. 
			 * Custom taxonomies still need to be registered with register_taxonomy().
			 */
			'taxonomies' => array(),
			'has_archive' => true,	// Enables post type archives. Will use $post_type as archive slug by default.
			/**
			 * Triggers the handling of rewrites for this post type. To prevent rewrites, set to false.
			 * 	'slug' => string Customize the permalink structure slug. Defaults to the $post_type value. Should be translatable.
			 * 	'with_front' => bool Should the permalink structure be prepended with the front base. 
			 *									(example: if your permalink structure is /blog/, then your links will be: false->/news/,  
			 *									true->/blog/news/). Defaults to true
			 *	'feeds' => bool Should a feed permalink structure be built for this post type. Defaults to has_archive value.
			 *	'pages' => bool Should the permalink structure provide for pagination. Defaults to true
			 *	'ep_mask' => const As of 3.4 Assign an endpoint mask for this post type.
			 */
			'rewrite' => array( 
				'slug' => $this->post_type,
				'with_front' => false,
				'feeds' => false,
				'pages' => true,
				'ep_mask' => EP_PERMALINK,
			),
			
			/**
			 * Sets the query_var key for this post type.
			 * true - set to $post_type
			 *				e.g. /?contact=hello
			 * false - Disables query_var key use. A post type cannot be loaded at /?{query_var}={single_post_slug}
			 * 'string' - /?{query_var_string}={single_post_slug} will work as intended.
			 *					e.g. if the string is 'abc', then e.g. /?abc=hello
			 */
			'query_var' => true,
			'can_export' => true,	// Can this post_type be exported.
		) );
		
		return $this;
	}
	
	/**
	 * Set CPT property value
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
	 * Set different parent post type
	 */
	function set_diff_parent_post_type(){
		$current_post_type = $this->post_type;
		$parent_post_type = $this->args['parent_post_type'];
		$self = $this;
		
		// Build parent meta box on edit.php
		add_action( 'add_meta_boxes_'.$current_post_type, function( $post ) use ( $current_post_type, $parent_post_type, $self ){
			// Remove build in metabox
			remove_meta_box( 'pageparentdiv', $current_post_type, 'side' );
			
			// Add new metabox
			add_meta_box( 'pageparentdiv2', __('Attributes'), array( $self, 'parent_meta_box' ), null, 'side', 'core' );
		});
		
		// Build parent dropdown in quick edit
		add_filter( 'quick_edit_dropdown_pages_args', function( $args ) use( $current_post_type, $parent_post_type ){
		
			global $post;
			
			if( !empty( $post ) && $post->post_type === $current_post_type ){
				$args['post_type'] = $parent_post_type;
			}
			
			return $args;
		});
	}
	
	function parent_meta_box( $post ){
		$args = array(
			'post_type'        => $this->args['parent_post_type'],
			'selected'         => $post->post_parent,
			'name'             => 'parent_id',
			'show_option_none' => __('(no parent)'),
			'sort_column'      => 'menu_order, post_title',
			'echo'             => 0,
		);
		
		$pages = wp_dropdown_pages( $args );
		if ( ! empty($pages) ) {
	?>
	<p><strong>Parent: <?php echo $this->args['parent_post_type'] ?></strong></p>
	<label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>
	<?php echo $pages; ?>
	<?php
		}
	}
	
	/**
	 * Register CPT
	 */
	function register(){
		
		// Register CPT if not register yet
		if( !$this->is_registered ){
			
			// Add labels to arguments
			$single_label = $this->single_label;
			$prural_label = $this->prural_label;
			$label_fn = function( $tmp ) use ( $single_label, $prural_label ){
				return sprintf( $tmp, $single_label, $prural_label );
			};
			$this->args['labels'] = array_map( $label_fn, $this->labels );			
			
			// Register CPT in init hook
			add_action( 'init', function( $this ){
				register_post_type( $this->post_type, $this->args );
				
				// Parent post type is different
				if( $this->args['hierarchical'] && $this->args['parent_post_type'] !== $this->post_type ){
					
					$this->set_diff_parent_post_type();
				}
			} );
			
			// CPT registered
			$this->is_registered = true;
		}
		
		return $this;
	}
	
}

/**
 * Function to manage omc cpt object
 * Please use this function instead of direct access
 */
function omc_cpt( $post_type, $single_label = '', $prural_label = '', $labels = array(), $args = array() ){
	
	global $omc_cpt_obj;
	
	// Create object if never created before
	if( !isset( $omc_cpt_obj[$post_type] ) )
		$omc_cpt_obj[$post_type] = new OMC_CPT( $post_type, $single_label, $prural_label, $labels, $args );
		
	return $omc_cpt_obj[$post_type];
}

/**
 * Make CPT seachable
 */
add_filter( 'pre_get_posts', 'omc_make_cpt_searchable', 13 );
function omc_make_cpt_searchable( $query ){

	if( !$post_types = get_query_var( 'post_types' ) ){
		if( isset( $_REQUEST['post_types'] ) )
			$post_types = $_REQUEST['post_types'];		
	}
	
	if( 
		$query->is_search && 
		!empty( $post_types ) &&
		( $post_types = explode( ',', $post_types ) ) &&
		!empty( $post_types )
	){
	
		$query->set( 'post_type', $post_types );
		
	};
	
	return $query;
};


do_action( 'omc_cpt_init' );
