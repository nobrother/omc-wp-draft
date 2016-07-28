var ga = ga || false;
(function(global, doc, $, ga){
	
	$(function(){
		var $btn = $('#back-to-top'),
				topStart = $btn.length && $btn.data('top-start') && parseInt($btn.data('top-start')) || 0;
		
		// Event: Click
		$btn.on('click', function(){
			// ga
			ga && ga( 'send', 'event', 'UI:navigation', 'click:#back-to-top', 'Scroll back to top' );	
		});
		
		// Event: Scroll
		$(global).on('scroll', function(e){
			$btn.toggleClass('active', (topStart < $(this).scrollTop()));
		})
	});
	
})(window, document, jQuery, ga);