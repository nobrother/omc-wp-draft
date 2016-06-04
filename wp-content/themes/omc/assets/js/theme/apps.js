/**
*	Apps js
*/
(function($, global, body){
	var momentList = global.momentList = function(key, eof, filters){
		var s = {},
				win = $(global),
				doc = $(body),
				eof = typeof eof === 'undefined' || eof,
				filters = filters || {},
				isScollActive = false,
				key = key,
				loading = $('#moment-list-loading');
		
		// Function to load next
		s.next = function(){
			
			// Stop if eof
			if(eof)
				return false;
			
			// Data											
			var data = {
				action: 'moment_get_next_page',
				key: key,
				filters: filters
			};
			
			// Show loading
			loading.toggleClass('active');
			
			// Request
			$.post(info.ajaxurl, data, function(response){
				//console.log(response);
				if(response.error){
					console.log(response);
					return false;
				}

				// Update
				eof = response.eof;
				
				var html = $(response.html);
				
				// Update FB stat
				updateFBStat.update(html);
				
				// Append new items
				$('#moment-list').append(html);
				
				// Hide Loading
				loading.toggleClass('active');
				
				// Waiting for next page
				if(!eof)
					loadScrollEvent();
			});

		}

		// Function to load scroll event
		var loadScrollEvent = function(){
			if(!isScollActive){

				// Event: scroll
				win.on('scroll.momentList', function(e) {
					if(win.scrollTop() + win.height() > doc.height() - 100) {
						win.off('scroll.momentList');
						isScollActive = false;
						//console.log('reach');

						s.next();
					}
				});

				isScollActive = true;
			}
		};

		// Initialize scroll event
		loadScrollEvent();

		return s;
	}
	
	// Update FB state
	var updateFBStat = global.updateFBStat = (function($){
		
		var s = {};
		
		s.cache = {};
		
		//Update
		s.update = function(el){
			
			var targets = {},
					hrefs = {};
			
			// Input must be jquery object
			if(!el || el.length == 0)
				return false;
			
			// Loop
			el.find('.share-count, .comment-count').each(function(i){
				
				var e = $(this),
						d = e.data('href');
				
				// Skip is href is empty
				if(!d)
					return true;
				
				if(!targets[d])
					targets[d] = { url: d };
				
				if(e.is('.share-count'))
					targets[d]['share-count'] = e;
				else
					targets[d]['comment-count'] = e;
				
				// Skip is href is cache
				if(s.cache[d]){
					s.assign(targets[d]);
					return true;
				}
				
				if(!hrefs[d])
					hrefs[d] = d;
				
			});
			
			// Request
			if(hrefs){
				
				for(key in hrefs){
					
					(function(s){
						var url = hrefs[key];

						$.get('http://graph.facebook.com/', 'id='+url, function(response){
							//console.log(response);

							if(response.error)
								return false;

							// Cache
							s.cache[url] = response;

							//console.log(s.cache);
							s.assign(targets[url]);
						})
					})(s);		
					
				}
			}
		}
		
		// Assign value from cache
		s.assign = function(target){
			if(!target || target.length == 0 || !(url = target.url))
				return false;
			
			if((e = target['share-count']) && e.length)
				e.html(s.cache[url].shares || 0);
			
			if((e = target['comment-count']) && e.length)
				e.html(s.cache[url].comments || 0);
		}
		
		return s;
	})($);
	
	/**
	 * EVENT: Like
	 */
	$(function(){
		$('body').on('click.moment', '.moment-item-like', function(e){
			e.preventDefault();
			
			var self = $(this),
					moment = self.closest('.moment-item'),
					mid = moment.data('id'),
					likeCount = moment.find('.like-count'),
					likeCountVal = parseInt(likeCount.html()) || 0;
			
			if(!mid)
				return false;
			
			var data = {
				'action': 'moment_toggle_like',
				'mid': mid
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
	});
})(jQuery, window, document)