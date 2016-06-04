<?php
// Check variable and set default
$default = array(
	'type' => 'general',
	'html_id' => 'back-to-top',
	'classes' => '',
	'top_start' => 300,
);

extract( $default, EXTR_SKIP );

?>
<a href="#" 
	 class="btn btn-back-to-top <?php esc_attr_e( $type )?> <?php esc_attr_e( $classes )?>" 
	 id="<?php esc_attr_e( $html_id ) ?>" 
	 data-scroll-to="body" 
	 data-top-start="<?php esc_attr_e( $top_start ) ?>">
	<i class="fa fa-angle-up"></i>
</a>