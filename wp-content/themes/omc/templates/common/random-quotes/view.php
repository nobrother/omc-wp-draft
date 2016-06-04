<?php 
	/*
	 * Little apps to return random quote
	 * Have FUN
	 * 
	 * Date: 11/09/2015
	 * Authot: Chang
	 * Version: 1.0.0
	 */	
	$quotes = array();
	$before = '<hgroup>';
	$after = '</hgroup>';
?>
<?php /* Quote START */ ob_start(); ?>
<h1 class="display-2">
	Those who went, this is what we achieved.
</h1>
<h1 class="display-2">
	Those who didn't, this is what happened.
</h1>
<?php $quotes[] = ob_get_clean(); /* Quote END */?>
<?php
// Echo
!empty( $l = count( $quotes ) ) && _e( $before.$quotes[rand( 0, --$l )].$after );