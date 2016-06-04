<header class="category-header">
	<div class="container">
		<h1>Category: <?php single_tag_title() ?></h1>
		<?php omc_inject( 'breadcrumb' ) ?>
	</div>
</header>

<div class="category-body two-col-right-sidebar">
	<div class="container">
		<div class="main-col"><?php omc_inject( 'post-list' ) ?></div>
		<div class="right-col"><?php omc_inject( 'sidebar/right-sidebar' ) ?></div>
	</div>
</div>