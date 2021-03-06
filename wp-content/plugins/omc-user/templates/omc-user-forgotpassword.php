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

			<h3><?php _e( 'Lost your password?', 'user-frontend-td' ); ?></h3>
			<?php echo apply_filters( 'omc_user_forgot_password_messages', isset( $_GET[ 'message' ] ) ? $_GET[ 'message' ] : '' ); ?>

			<form action="<?php echo OMC_User::get_action_url( 'forgot_password' ); ?>" method="post">
				<?php wp_nonce_field( 'forgot_password' ); ?>
				<p>
					<?php _e( 'Please enter your username or email address. You will receive a link to create a new password via email.' ) ?>
				</p>
				<p>
					<label for="user_login"><?php _e( 'Username or E-mail:' ); ?></label>
					<input type="text" name="user_login" id="user_login">
				</p>
				<p>
					<input type="submit" name="submit" id="submit" value="<?php _e( 'Get New Password' ); ?>">
				</p>
			</form>
		</div>
	</div>
</div>

<?php get_footer(); ?>
