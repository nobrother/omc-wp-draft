var apps = apps || {};

(function($, window, bb, _, apps, info){
	
	// Define a model with some validation rules
	var registerModel = bb.Model.extend({
		defaults:{
			user_login: '',
			user_email: '',
			user_password: ''
		},
		validation: apps.users.validationRules(
			['user_login','user_email', 'user_password'],
			{
				user_login: {
					fn: function(value, attr, computedState){
						if(value.length >= 3){
							if(this.checkUsernameXhr)
								this.checkUsernameXhr.abort();

							this.checkUsernameXhr = $.ajax({
								data: {
									action: 'omc_user_check_new_username',
									value: value
								},
								complete: function(){
									
								}
							});
						}
						
						if ('undefined' === typeof this.userExists || -1 === this.userExists) 
							return " ";
						else if (1 === this.userExists)
							return "This username is taken, please pick another one.";
					}
				}
			}
		)
	});

	// Define a View that uses our model
	var registerView = bb.View.extend({
		events: {
			'submit': 'submit',
			'change #password': 'updateOnChanged',
			'keyup #username': 'updateOnChanged'
		},

		initialize: function () {
			// This hooks up the validation
			bb.Validation.bind(this);
			
			this.listenTo(this.model, 'change:user_password', this.validate);
			this.listenTo(this.model, 'change:user_login', this.validateUsername);
		},

		submit: function(e){
			e.preventDefault();
			this.register();
		},
		
		register: function () {
			// Check if the model is valid before saving
			if(this.model.isValid(true)){
				var data = $.extend({}, this.$el.serializeObject(), this.model.toJSON());
				
				apps.overlay.set('type', 'loading').show();
				
				$.ajax({
					data: data,
					success: function(response){
						//console.log(response);
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
					model.isValid(key, true);
				})
			}
		},
		
		// Send ajax to validate username
		validateUsername: function(model){
			var delay = this.inputDelay('username');
			
			delay(function(){
				// First check if the username is valid or not
				if(!model.isValid('user_login', true))
					return;
				
				
			}, 300);
		},
		
		// Helper function to declare delay
		inputDelay: function(key){
			if(!key) return false;
			
			this.inputDelayData = this.inputDelayData || {};			
			
			if(this.inputDelayData && this.inputDelayData[key])
				return this.inputDelayData[key];
			
			return this.inputDelayData[key] = window.delay();
		}
	});

	$(function () {
		var view = new registerView({
			el: '#form-register',
			model: new registerModel()
		});
	});

})(jQuery, window, Backbone, _, apps, info);