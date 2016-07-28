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
	});
	
	/**
	 * EVENT: Google Analytic Event
	 */
	var ga = ga || false;
	$(function(){
		$('body').on('click', '.ga', function(e){
			if(!ga)
				return;

			var el = e.currentTarget,
					$el = $(el),
					category = $el.data('ga-category') || 'Unknown event',
					action = $el.data('ga-action') || el.id && ('click:#'+el.id) || 'click',
					label = $el.data('ga-label') || el.href || '',
					value = $el.data('ga-value') || 0;

			ga('send', 'event', {
				eventCategory: category,
				eventAction: action,
				eventLabel: label,
				eventValue: value,
				transport: 'beacon'		// Handle outbound links
			});
		});
	})

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