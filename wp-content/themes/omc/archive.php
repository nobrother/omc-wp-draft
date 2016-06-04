<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package omc
 */

get_header( 'site' ); ?>

	<div id="primary" class="content-area <?php !have_posts() && _e( 'archive-not-found' ) ?>">
		<main id="main" class="site-main" role="main">

			<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
					the_archive_title( '<h1>', '</h1>' );
					the_archive_description( '<div class="description">', '</div>' );
				?>
			</header><!-- .page-header -->
		
			<div class="page-content">
			<?php 
				// The Loop
				while ( have_posts() ){
					the_post(); 
					get_template_part( 'content', get_post_format() );
				}

				// Pagination	
				the_posts_navigation();
			?>
			</div>

			<?php else : ?>
			
				<?php get_template_part( 'content', 'main-not-found' ); ?>

			<?php endif; ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar( 'site' ); ?>
<?php get_footer( 'site' ); ?>
