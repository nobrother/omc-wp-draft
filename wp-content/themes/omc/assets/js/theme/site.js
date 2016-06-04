(function(global, doc, $){
	// Event: Goto
	$(function(){
		$('body').on('click.scrollTo', '[data-scroll-to]', function(e){
			
			var selector = $(this).data('scroll-to') || '',
					target = $(selector).first();
			
			if(target.length){
				e.preventDefault();
				target.scrollTo(500);
			}
		});
		
		// EVENT: Show/hide back to top button
		(function(global, $){
			var $btn = $('#back-to-top'),
					topStart = $btn.length && $btn.data('top-start') && parseInt($btn.data('top-start')) || 0;
			$(global).on('scroll', function(e){
				$btn.toggleClass('active', (topStart < $(this).scrollTop()));
			})
		})(global, $);
		
	});
	
	/**
	 * EVENT: Like
	 */
	$(function(){
		$('body').on('click.post', '.js-post-like', function(e){
			e.preventDefault();
			
			var self = $(this),
					post = self.closest('.js-post'),
					pid = post.data('id'),
					likeCount = post.find('.js-post-like-count'),
					likeCountVal = parseInt(likeCount.html()) || 0;

			if(!pid)
				return false;
			
			var data = {
				'action': 'omc_post_toggle_like',
				'pid': pid
			}
			
			// Request
			$.post(info.ajaxurl, data, function(response){
				//console.log(response);
			});
			
			// Toggle class
			if(self.toggleClass('active').is('.active'))
				likeCount.html(++likeCountVal);
			
			else{
				likeCount.html(--likeCountVal);
				/*
				if(--likeCountVal == 0)
					likeCount.html('');
				else
					likeCount.html(likeCountVal);
				*/
			}
		});
	})
})(window, document, jQuery);