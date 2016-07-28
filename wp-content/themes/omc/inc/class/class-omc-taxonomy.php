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
		 */
		$this->args = $args;
		
		return $this;
	}
	
	/**
	 * Set taxonomy like category
	 */
	function set_like_category(){
		$this->args = array( 'hierarchical' => true ) + $this->args;
		return $this;
	}
	
	/**
	 * Set taxonomy like tag
	 */
	function set_like_tag(){
		$this->args = array( 'hierarchical' => false ) + $this->args;
		return $this;
	}
	
	/**
	 * Set taxonomy property value
	 */
	function set( $property_name, $value ){
		
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
			
		return $this->set( $name, array_replace_recursive( $this->args, $args ) );
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
			
			// taxonomy registered
			$this->is_registered = true;
		}
		 
		return $this;
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
