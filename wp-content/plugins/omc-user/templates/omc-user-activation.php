<?php
// **********************************************************
// If you want to have an own template for this action
// just copy this file into your current theme folder
// and change the markup as you want to
// **********************************************************
if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/'.OMC_User::$page_slugs['profile'].'/' ) );
	exit;
}
?>
<?php get_header(); ?>

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<h3><?php echo 'Activation' ?></h3>
			<?php echo apply_filters( 'omc_user_activation_messages', isset( $_GET[ 'message' ] ) ? $_GET[ 'message' ] : '' ); ?>

			<form action="<?php echo OMC_User::get_action_url( 'activation' ); ?>" method="post">
				<?php wp_nonce_field( 'activation', 'wp_nonce' ); ?>

				<p>
					<?php echo 'Please enter your key.' ?>
				</p>
				<p>
					<label for="user_key"><?php echo 'Key:' ?></label>
					<input type="text" name="user_key" id="user_key" value="<?php echo isset( $_GET[ 'key' ] ) ? $_GET[ 'key' ] : ''; ?>">
				</p>
				<p>
					<input type="submit" name="submit" id="submit" value="<?php echo 'Activate' ?>">
				</p>
			</form>
		</div>
	</div>
</div>

<?php get_footer(); ?>
