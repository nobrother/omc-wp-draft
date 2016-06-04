<?php
/**
 * The header for our theme.
 */
?><!DOCTYPE html>

<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php 
		do_action( 'omc_title' );
		do_action( 'omc_meta' );
		do_action( 'omc_before_head' );

		wp_head(); 

		do_action( 'omc_after_head' );

		// Load top script
		echo eval( '?>' . omc_get_option( 'top_script' ) . '<?php ' );
	?>

</head>

<body <?php echo apply_filters( 'omc_body_attribute', '' ); ?>>
	
	<?php 
		// Usually we put facebook snippet here
		do_action( 'omc_begining_of_body' ); 
	?>

	