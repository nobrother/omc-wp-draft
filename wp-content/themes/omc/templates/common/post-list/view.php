<div class="post-list">
	<?php 
		while ( have_posts() ){
			the_post();
			include 'post-list-item.php';
		}

		omc_inject( 'pagination' ); 
	?>
</div>