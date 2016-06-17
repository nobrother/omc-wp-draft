<?php
/**
 * The header for our theme.
 */

// Load fonts
load_external_font( 'source-san-pro', 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400italic,700,700italic' );

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

		// Load top script
		echo eval( '?>' . omc_get_option( 'top_script' ) . '<?php ' );
	?>

</head>

<body <?php echo apply_filters( 'omc_body_attribute', '' ); ?>>
	
	<!-- Facebook snippet --
	<div id="fb-root"></div>
	<script>
		window.fbAsyncInit = function() {
			FB.init({
				appId      : '1158888177478090',
				xfbml      : true,
				version    : 'v2.5'
			});
		};
		(function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));</script>
	<!-- / Facebook snippet -->	
	
	<?php 
		// Usually we put facebook snippet here
		do_action( 'omc_begining_of_body' ); 
	?>

	