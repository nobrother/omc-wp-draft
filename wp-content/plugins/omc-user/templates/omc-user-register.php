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
get_header(); ?>

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<h2><?php _e( 'Register', 'user-frontend-td' ); ?></h2>
			<?php echo apply_filters( 'omc_user_register_messages', isset( $_GET[ 'message' ] ) ? $_GET[ 'message' ] : '' ); ?>

			<form action="<?php echo OMC_User::get_action_url( 'register' ); ?>" method="post">
				<?php wp_nonce_field( 'register' ); ?>

				<table class="form-table">
					<tr>
						<th><label for="user_login"><?php _e( 'Username' ); ?></label></th>
						<td><input type="text" name="user_login" id="user_login" class="regular-text" required/> <span class="description"><?php _e( 'Usernames cannot be changed.' ); ?></span></td>
					</tr>
					<tr>
						<th><label for="user_email"><?php _e( 'E-mail' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
						<td><input type="text" name="user_email" id="user_email" class="regular-text" required/></td>
					</tr>
					<tr>
						<th><label for="user_pass"><?php _e( 'Password' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
						<td><input type="text" name="user_pass" id="user_pass" size="16" class="regular-text" required /></td>
					</tr>
				</table>

				<input type="submit" name="submit" id="submit" value="<?php _e( 'Register', 'user-frontend-td' ); ?>">

			</form>
		</div>
	</div>
</div>

<?php get_footer(); ?>