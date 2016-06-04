<?php
// Check variable and set default
$default = array(
	'type' => 'general',
	'html_id' => '',
	'classes' => 'block-sub1',
	'content' => '',
	'author' => '',
);

extract( $default, EXTR_SKIP );

?>
<div class="block block-quote <?php esc_attr_e( $type )?> <?php esc_attr_e( $classes )?>" id="<?php esc_attr_e( $html_id ) ?>">
	<div class="block-content">
		<div class="container">
			<blockquote>
				<p><?php echo $content ?></p>
				<?php if($author): ?>
				<div class="author"><?php echo $author ?></div>
				<?php endif ?>
			</blockquote>
		</div>
	</div>
</div>