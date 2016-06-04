// Overlay

var overlay;
(function($){

	overlay = function(){
		var self = overlay;

		self.el = jQuery('<div>').addClass('overlay').hide().appendTo('body');
		self.el.parent = self;

		self.hide = function(speed, delay){
			var speed = speed || 300;
			var delay = delay || 0;

			self.el.stop(true, true).delay(delay).fadeOut(speed);
			return self;
		}

		self.show = function(speed, delay){
			var speed = speed || 300;
			var delay = delay || 0;

			self.el.stop(true, true).delay(delay).fadeIn(speed);

			return self;
		}

		self.addText = function(text){
			text &&	self.el.html($('<span>').addClass('big-text').html(text));

			return self;
		}

		return self;
	};

	

	// Initiate overlay object

	$(function(){

		overlay();

	})

})(jQuery);