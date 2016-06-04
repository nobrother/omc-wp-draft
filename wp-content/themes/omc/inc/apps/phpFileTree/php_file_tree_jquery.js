(function($){
	$(function(){
		// Expand/collapse on click
		$('.pft-directory > a').on('click', function(e) {
			e.preventDefault();
			$(this).next('ul').toggle();
		});
		
		// Show active
		$('.pft-file.active')
			.parentsUntil('.php-file-tree', '.pft-directory')
			.find("UL:first")
			.show()
	});	
})(jQuery);