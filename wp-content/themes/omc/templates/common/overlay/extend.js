var apps = apps || {};

(function($, window, bb, _, apps, info){
	var overlay = apps.overlay = {
		hide: function(speed, delay, callback){
			var speed = speed || 300;
			var delay = delay || 500;

			this.view.$el.delay(delay).fadeOut(speed, callback);
			
			return this;
		},

		show: function(speed, delay, callback){
			var speed = speed || 300;
			var delay = delay || 0;

			this.view.$el.delay(delay).fadeIn(speed, callback);
			
			return this;
		},
		
		set: function(attributes, options){
			this.view.model.set(attributes, options);

			return this;
		}
	};
	
	var overlayModel = bb.Model.extend({
		defaults:{
			type: 'bigText',
			text: ''
		}
	});
	
	var ovelayView = bb.View.extend({
		// Main view DOM
		el: '#overlay',
		
		// Load all templates
		templates:{
			bigText: _.template($('#tpl-overlay-big-text').html()),
			loading: _.template($('#tpl-overlay-loading').html())
		},
		
		// Initialize: Set event listener
		initialize: function () {
			// Re render whenever model change
			this.listenTo(this.model, 'change', this.render);
		},
		
		render: function(){
			var type = this.model.get('type'),
					template = this.templates[type] || {};
			
			if(template)
				this.$('.overlay-content').html(template(this.model.toJSON()));
		}
	});
	
	
	
	// Initialize
	$(function(){
		overlay.view = new ovelayView({
			model: new overlayModel()
		});
	});
})(jQuery, window, Backbone, _, apps, info);