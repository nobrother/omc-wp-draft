<?php omc_include_file( __DIR__.'/header.php' ) ?>

<!-- Main menu -->
<div id="main-menu">
	<?php omc_inject( 'nav' );  ?>
</div>
<!-- / Main menu -->

<!-- Main Content -->
<div id="content">
	<?php omc_include_file( __DIR__.'/content.php' ) ?>
</div><!-- / Main Content -->

<!-- Ending -->
<?php omc_inject( 'footer', false ); ?>
<!-- / Ending -->