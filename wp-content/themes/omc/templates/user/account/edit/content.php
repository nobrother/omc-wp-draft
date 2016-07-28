<?php global $this_user; ?>
<div class="block">
	<div class="container">
		<header class="block-header page-head">
			<h1>Account</h1>
		</header>
		
		<form class="user-form user-form-account form" id="form-account">
			<?php omc_nonce_field( 'omc_user_edit_account', $this_user->email ) ?>
			<input type="hidden" name="uid" value="<?php esc_attr_e( $this_user->id ) ?>">
			
			<div class="form-group">				
				<label for="avatar">Avatar</label>
				<figure class="avatar">
					<img 
							 id="avatar-img"
							 src="<?php echo $this_user->get_avatar() ?>"
							 >
					<a class="link" id="avatar-change">Change</a>
				</figure>				
				<span class="help-block hidden"></span>
				<input type="hidden" name="avatar" id="avatar-value" 
							 value="<?php esc_attr_e( $this_user->avatar ) ?>"
							 >
			</div>
			
			<div class="form-group">				
				<label for="display-name">Your name</label>
				<input 
							 type="text" name="display_name" class="form-control" id="display-name" placeholder="Your name" 
							 value="<?php esc_attr_e( $this_user->display_name ) ?>"
							 >
				<span class="help-block hidden"></span>
			</div>
			
			<div class="form-group">				
				<label for="description">Your cool story</label>
				<textarea 
							 name="description" class="form-control" id="description" placeholder="Cool story" 
							 ><?php echo esc_textarea( $this_user->description ) ?></textarea>
				<span class="help-block hidden"></span>
			</div>
			<button type="submit" class="btn btn-primary">Submit</button>
		</form>
	</div>
</div>