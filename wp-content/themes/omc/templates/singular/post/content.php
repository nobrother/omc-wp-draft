<?php
namespace OMC\Post_Type\Post;

global $post;

$post_obj = new Object( $post );
$data = array( 'post_obj' => $post_obj );
the_post();
?>
<!-- BLOCK: INTRO -->
<article 
				 id="post-<?php the_ID(); ?>" 
				 <?php post_class( 'post js-post' ); ?>
				 data-id="<?php the_ID() ?>"
				 >
	<div class="container">

		<header class="post-header">
			<div class="numbering">
				<span class="prefix">Idea</span>
				<span class="number">#<?php esc_html_e( sprintf( "%'.03d", $post_obj->numbering ) ) ?></span>
			</div>
		</header>

		<div class="post-content">
			<?php the_content(); ?>
		</div>

		<footer class="post-footer">
			<div class="divider"></div>
			
			<?php omc_inject( 'post-meta', true, $data ) ?>
			<?php //omc_inject( 'post-actions', true, $data ) ?>
			<?php //omc_inject( 'post-social', true, $data ) ?>

			<?php 			
				//edit_post_link( sprintf('Edit "%s"', get_the_title() ), '<div class="edit-link" target="_blank">', '</div>' );

				// If comments are open or we have at least one comment, load up the comment template.
				//if ( comments_open() || get_comments_number() ) {
				//	comments_template();
				//}
			?>		
		</footer>
	</div>
</article>