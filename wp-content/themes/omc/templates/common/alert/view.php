<?php
// Check variable and set default
$default = array(
	'type' => 'general',
	'html_id' => '',
	'image' => '!',
	'header' => '',
	'body' => '',
	'footer' => '',
);

extract( $default, EXTR_SKIP );
?>
<div class="alert <?php esc_attr_e( $type ) ?>">
	<div class="alert-content">
		<div class="alert-left">
			<div class="alert-image">
				<?php if( is_url( $image ) ): ?>
				<img src="<?php esc_attr_e( $image ) ?>">
				<?php else: ?>
				<?php echo $image ?>
				<?php endif ?>
			</div>
		</div>
		<div class="alert-right">
			<div class="alert-header"><?php echo $header ?></div>
			<div class="alert-body"><?php echo $body ?></div>
			<?php if( !empty( $footer ) ): ?>
			<div class="alert-footer"><?php echo $footer ?></div>
			<?php endif ?>
		</div>
	</div>
</div>