<?php

namespace OMC\Post;

global $post;
$post_obj = new Post( $post );
$data = array( 'post_obj' => $post_obj );
?>

<article
	id="post-<?php the_ID(); ?>"
	<?php post_class( 'post-list-item js-post' ); ?>
	data-id="<?php the_ID() ?>"
>
	<header class="post-list-item-header">
		<h1 class="hidden"><?php the_title() ?></h1>
		<a href="<?php the_permalink(); ?>"><?php printf( "#%'.03d", $post_obj->numbering ) ?></a>
	</header>

	<div class="post-list-item-body">
		<p><a href="<?php the_permalink(); ?>"><?php echo $post->post_excerpt ?></a></p>
	</div>

	<footer class="post-list-item-footer">
		<div class="tags-wrap">
			<?php echo $post_obj->get_tags_list(); ?>
		</div>
	</footer>
</article>