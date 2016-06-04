<?php
// Check variable and set default
if( !isset( $type ) )
	$type = 'focus-contact';
$default = array(
	'html_id' => '',
	'title' => '',
	'description' => '',
	'chat_title' => 'Let’s Chat Online',
	'forum_title' => 'Ask on Forum',
	'btn_forum_title' => 'Check out the Forum',
	'contact_title' => 'Get In Touch',
	'contact_email' => 'chun@ohmycode.com.my',
	'contact_phone' => '017-6260089',
	'contact_address' => 'Puchong, Selangor'
);

switch( $type ){
	case 'focus-forum':
	$default['chat_title'] = 'Chat Online with Us';
	$default['forum_description'] ='Have a general question? <br> 
		Want to know how stuffs works?<br> 
		Go to our forum for info or ask your question there for us to answer!';
	break;
	case 'focus-contact': default:
		$default['forum_description'] ='Have a general question? Go to our forum for info or to throw your question there for us to answer!';
}

extract( $default, EXTR_SKIP );

?>
<div class="block block-contact <?php esc_attr_e( $type )?>" id="<?php esc_attr_e( $html_id ) ?>">
	<div class="container">
		<header class="block-header">
			<h1><?php echo $title ?></h1>
			<p><?php echo $description ?></p>
		</header>
		
		<div class="block-body">
			
			<div class="row-flexbox">
				<div class="col-left free">
					<section class="col-left-content chat-app">
						<h1><?php echo $chat_title ?></h1>
						<?php omc_inject( 'twak-chat-app' ) ?>
					</section>
				</div>
				<div class="col-right">
					<?php if( 'focus-forum' == $type ): ?>
					<section class="col-right-content ask-forum">
						<h1><?php echo $chat_title ?></h1>
						<p><?php echo $forum_description ?></p>
						<a href="#" class="btn btn-primary"><?php echo $btn_forum_title ?></a>
					</section>
					<?php else: ?>
					<section class="col-right-content get-in-touch">
						<h1><?php echo $contact_title ?></h1>
						<address>
							<ul class="list list-fa">
								<li><i class="fa fa-envelope"></i><?php echo $contact_email ?></li>
								<li><i class="fa fa-phone"></i><?php echo $contact_phone ?></li>
								<li><i class="fa fa-map-marker"></i><?php echo $contact_address ?></li>
							</ul>
						</address>
					</section>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
		
		<section class="block-footer">
			<?php if( 'focus-forum' == $type ): ?>
			<div class="footer-content get-in-touch">
				<address class="row-inline-block justify vertical-middle">
					<div class="title"><h1><?php echo $contact_title ?></h1></div>
					<div class="email">
						<i class="fa fa-envelope"></i>
						<span><a href='mailto:<?php echo $contact_email ?>'><?php echo $contact_email ?></a></span>
					</div>
					<div class="phone">
						<i class="fa fa-phone"></i>
						<span><?php echo $contact_phone ?></span>
					</div>
				</address>
				<p>
					<i class="fa fa-map-marker"></i>
					<span>We are based in Puchong, Selangor.  We would be happy to work with you, anywhere online. </span>
				</p>
			</div>
			<?php else: ?>
			<div class="footer-content ask-forum">
				<div class="row-inline-block justify vertical-middle">
					<div class="title"><h1><?php echo $forum_title ?></h1></div>
					<div class="description"><?php echo $forum_description ?></div>
					<div class="goto-forum">
						<a href="#" class="btn btn-primary"><?php echo $btn_forum_title ?></a>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</section>
	</div>
</div>