<div class="block">
	<div class="container">
		<header class="block-header page-head">
			<h1>Register</h1>
		</header>
		
		<form class="user-form user-form-register" id="form-register">
			<?php omc_nonce_field( 'omc_user_register' ) ?>
			<div class="form-group">				
				<label for="username">Username</label>
				<input type="text" name="user_login" class="form-control" id="username" placeholder="Username">
				<span class="help-block hidden"></span>
			</div>
			<div class="form-group">				
				<label for="email">Email</label>
				<input type="text" name="user_email" class="form-control" id="email" placeholder="Email">
				<span class="help-block hidden"></span>
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input type="text" name="user_password" class="form-control" id="password" placeholder="Password">
				<span class="help-block hidden"></span>
			</div>
			
			<button type="submit" class="btn btn-primary">Create an account</button>
		</form>
	</div>
</div>