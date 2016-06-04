<?php

if ( post_password_required() || is_attachment() || ! has_post_thumbnail() )
	return;

if ( is_singular() ) { // For single post ?>

<div class="post-thumbnail">
	<?php the_post_thumbnail(); ?>
</div>

<?php } else { ?>

<a class="post-list-item-thumbnail" href="<?php the_permalink(); ?>">
	<?php the_post_thumbnail( 'post-thumbnail', array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
</a>

<?php }