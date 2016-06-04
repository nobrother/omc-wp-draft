<div class="post-social">			
	<!-- Like count-->
	<div class="post-like-count">
		<span class="like-count js-post-like-count">
			<?php	
				if( !is_wp_error( $liked = $post_obj->is_liked() ) ){ 
				esc_html_e( $post_obj->like_count );
				} else {
				echo 0;
				}
			?>
		</span>
		<span>like(s)</span>
	</div><!-- / Like count -->
	
	<div class="post-comments-count">
		<span class="comments-count"><?php _s( get_comments_number(), 'comment', 'comments' ) ?></span>
	</div>
</div>

