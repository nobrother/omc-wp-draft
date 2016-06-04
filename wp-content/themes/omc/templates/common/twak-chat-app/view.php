<?php
$default = array(
	'html_id' => '',
	'api_app_key' => '5724562a83f240f80925dfc4',
	'api_embed_key' => '1ahivmvrm',
);
extract( $default, EXTR_SKIP );

?>
<div class="chat-app-wrap" id="<?php esc_attr_e( $html_id ) ?>">
	<div id='tawk_<?php echo $api_app_key ?>'></div>
	<!--Start of Tawk.to Script-->
	<script type="text/javascript">
		var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date(); Tawk_API.embedded='tawk_<?php echo $api_app_key ?>';
		(function(){
			var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
			s1.async=true;
			s1.src='https://embed.tawk.to/<?php echo $api_app_key ?>/<?php echo $api_embed_key ?>';
			s1.charset='UTF-8';
			s1.setAttribute('crossorigin','*');
			s0.parentNode.insertBefore(s1,s0);})();
	</script>
	<!--End of Tawk.to Script-->
</div>