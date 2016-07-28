<?php global $this_user; ?>
<div class="block">
	<div class="container">
		<header class="block-header page-head">
			<h1>Password</h1>
		</header>
		
		<form class="user-form user-form-password form" id="form-password">
			<?php omc_nonce_field( 'omc_user_change_password', $this_user->email ) ?>
			<input type="hidden" name="uid" value="<?php esc_attr_e( $this_user->id ) ?>">
			
			<div class="form-group">				
				<label for="username">Old Password</label>
				<input type="text" name="old_password" class="form-control" id="old-password" placeholder="Old password">
				<span class="help-block hidden"></span>
			</div>
			<div class="form-group">
				<label for="password">New password</label>
				<input type="text" name="user_pass" class="form-control" id="new-password" placeholder="New password">
				<span class="help-block hidden"></span>
			</div>			
			<button type="submit" class="btn btn-primary">Submit</button>
		</form>
	</div>
</div>