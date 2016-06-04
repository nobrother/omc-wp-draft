/**
 * Push menu
 */
(function($){
	$(function(){
		
		var togglePushMenu = function(menu){
			if(!menu || !menu.length)
				return;
			
			menu.toggleClass('active');
		}
		
		$('body').on('click touchend', '.push-menu-btn, .push-menu-close', function(e){
		
			e.preventDefault();
			var self = $(this),
					pushMenu,
					href;
			
			// Looking for cache
			if(!(pushMenu = self.data('push-menu'))){
				
				// Looking in href
				if((href = self.data('href')) && (pushMenu = $(href)) && pushMenu.length)
					self.data('push-menu', pushMenu);
				
				// Looking in parents
				else if((pushMenu = self.closest('.push-menu')) && pushMenu.length)
					self.data('push-menu', pushMenu);
				
				// Nothing
				else
					return;
			}
			
			togglePushMenu(pushMenu);
			
		})
		
		// Click others to close
		.on('click touchend', '.push-menu', function(e){
			if($(e.target).is('.push-menu')){
				e.preventDefault();
				togglePushMenu($(e.target));
			}
		});
	})
})(jQuery);