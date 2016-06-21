<div class="block">
	<div class="container">
		<header class="block-header page-head">
			<h1>Login</h1>
		</header>
		
		<form class="user-form user-form-login" id="form-login">
			<?php omc_nonce_field( 'omc_user_login' ) ?>
			<div class="form-group">				
				<label for="username">Username / Email address</label>
				<input type="text" name="user_login" class="form-control" id="username" placeholder="Email">
				<span class="help-block hidden"></span>
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input type="text" name="user_password" class="form-control" id="password" placeholder="Password">
				<span class="help-block hidden"></span>
			</div>
			<div class="form-group">				
				<div class="checkbox"> 
					<label>
						<input type="checkbox" name="remember" id="remember" checked value="1">
						Keep me log in
					</label> 
				</div>
			</div>
			
			<button type="submit" class="btn btn-primary">Submit</button>
		</form>
	</div>
</div>