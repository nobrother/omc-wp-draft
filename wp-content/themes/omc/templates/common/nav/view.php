<nav class="navbar navbar-static-top navbar-default hidden" id="top-nav" role="banner">
	<div class="nav-content">
		<div class="container">			
			<!-- 			
			=================			
			Top Menu Header 			
			=================			
			# Contain Logo and menu push button			
			# Will display fully on mobile version			
			-->			
			<div class="navbar-header">
				<!-- Logo / Brand / Home button -->
				<a href="<?php _e( site_url() ) ?>" class="navbar-brand">
					<div class="main-logo">
						<img src="https://unsplash.it/196/109?image=798">
					</div>				
				</a>				
				<!-- / Logo / Brand / Home button -->			
			</div>			<!-- / Top Menu Header -->			

			<!-- 			
			=================			
			Top Menu Listing			
			=================			
			# Contain all the pages / items to navigate			
			# Will hide on mobile version and display when user push the button			
			-->			
			<nav id="top-menu" class="collapse navbar-collapse">				
				<!-- BUTTON: Contact -->				
				<ul class="nav navbar-nav navbar-right">					
					<li id="nav-item-talk-to-us">						
						<a href="#" data-scroll-to=".block-contact">
							<span>Let's talk</span>
						</a>					
					</li>				
				</ul><!-- / BUTTON: Contact -->								
				<!-- Menu Items on the right hand side -->				
				<?php wp_nav_menu(					
						array(						
							'theme_location'  => 'top-right',						
							'container'       => '',						
							'fallback_cb'			=> false,						
							'menu_class'      => 'nav navbar-nav navbar-right',					
						)				
					); 				
				?>				
				<!-- / Menu Items on the right hand side -->			
			</nav><!-- / Top Menu Listing -->
			
			<div class="divider"></div>
			
		</div><!-- // CONTAINER -->		
	</div><!-- // NAV CONTENT -->	
</nav><!-- // Top Nav -->	