var apps = apps || {};

(info && 'user_login' === info.url_id) && (function($, window, bb, _, apps, info){
	
	// Define a model with some validation rules
	var loginModel = bb.Model.extend({
		defaults:{
			user_login: '',
			user_pass: ''
		},
		validation: apps.users.validationRules(['user_login', 'user_pass'], false)
	});

	// Define a View that uses our model
	var loginView = bb.View.extend({
		events: {
			'submit': 'submit',
			'change': 'updateOnChanged'
		},

		initialize: function () {
			// This hooks up the validation
			bb.Validation.bind(this);
			
			this.listenTo(this.model, 'change', this.validate);
		},

		submit: function(e){
			e.preventDefault();
			this.login();
		},
		
		login: function () {
			var view = this;
			
			// Check if the model is valid before saving
			this.model.isValid(true, function(){
				
				var data = $.extend({}, view.$el.serializeObject(), view.model.toJSON());
				
				apps.overlay.set('type', 'loading').show();
				
				$.ajax({
					data: data,
					success: function(response){
						console.log(response);
						if('1' === response.status){
							apps.overlay.set({type: 'bigText', text: 'You are in!'}).hide()
							
								// redirect
								.view.$el.queue(function(){
								if(response.redirect_to)
									window.location = response.redirect_to;
								$(this).dequeue();
							});
							
						}
						else
							apps.overlay.set({type: 'bigText', text: response.error}).hide(300, 5000);
					}
				});
			});
		}
	});

	$(function () {
		// Load the apps if the form exists
		var view = new loginView({
			el: '#form-login',
			model: new loginModel()
		});
	});

})(jQuery, window, Backbone, _, apps, info);