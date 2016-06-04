<?php 

namespace OMC\Post;

global $post;
$post_obj = new Post( $post );
$data = array( 'post_obj' => $post_obj );
the_post();
?>

<article 
	id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post js-post' ); ?>
	data-id="<?php the_ID() ?>"
>
	<?php omc_inject( 'post-thumbnail' ); ?>

	<div class="post-content clearfix">
		<?php the_content(); ?>
	</div>

	<footer class="post-footer">
		
		<?php omc_inject( 'post-meta', true, $data ) ?>
		
		<?php omc_inject( 'post-actions', true, $data ) ?>
		
		<!-- Likes counts, Comment Count, Share Count -->
		<?php omc_inject( 'post-social', true, $data ) ?>
		<!-- / Likes counts, Comment Count, Share Count -->

		<?php 			
			edit_post_link( sprintf('Edit "%s"', get_the_title() ), '<div class="edit-link" target="_blank">', '</div>' );
			
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		?>		
	</footer>
</article>