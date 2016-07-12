<?php
/**
 * The header for our theme.
 */

// Load fonts
load_external_font( 'fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css' );
load_external_font( 'source-san-pro', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400italic,700,700italic' );
load_external_font( 'montserrat', 'https://fonts.googleapis.com/css?family=Montserrat:400,700' );
load_external_font( 'open-san', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' );
load_external_font( 'fh', OMC_FONTS_URL.'/font-heads/font-heads.css' );

// Load scripts
add_action( 'wp_enqueue_scripts', 'omc_load_backbone' );

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
	?>
	
	<!-- Google Analytic -->
	<?php if( !is_user_logged_in() ) { ?>
	<script>
		// Put Google analytic snippet here
	</script>
	<?php } ?>
	<!-- // Google Analytic -->

</head>

<body <?php echo apply_filters( 'omc_body_attribute', '' ); ?>>
	
	<?php 
		// Usually we put facebook snippet here
		do_action( 'omc_begining_of_body' ); 
	?>

	