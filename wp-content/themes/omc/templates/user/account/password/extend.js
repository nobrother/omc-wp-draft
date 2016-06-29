var apps = apps || {};

(function($, window, bb, _, apps, info){
	
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
			// Check if the model is valid before saving
			if(this.model.isValid()){
				var data = $.extend({}, this.$el.serializeObject(), this.model.toJSON());
				
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
			}
		},

		updateOnChanged: function(e){
			var input = $(e.target),
					name = input.attr('name') || '';
			
			if(name)
				this.model.set(name, input.val());
		},
		
		validate: function(model){
			if(model.changed){
				$.each(model.changed, function(key, value){
					model.isValid(key);
				})
			}
		}
	});

	$(function () {
		// Load the apps if the form exists
		if($('#form-login').length){
			var view = new loginView({
				el: '#form-login',
				model: new loginModel()
			});
		}
	});

})(jQuery, window, Backbone, _, apps, info);