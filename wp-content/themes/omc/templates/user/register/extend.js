var apps = apps || {};

(info && 'user_register' === info.url_id) && (function($, window, bb, _, apps, info){
	
	// Define a model with some validation rules
	var registerModel = bb.Model.extend({
		defaults:{
			user_login: '',
			user_email: '',
			user_pass: ''
		},
		validation: apps.users.validationRules(
			['user_login','user_email', 'user_pass'],
			{
				user_login: {
					async: true,
					msg: "This username is taken, please pick another one."	,
					remote: function(value, attr, model, computed, deferred){
						$.ajax({
							data: {
								action: 'omc_user_check_new_username',
								value: value
							},
							success: function(response){
								if("1" === response.status)
									deferred.resolve();
								else
									deferred.reject();
							},
							error: function(){
								deferred.reject();
							}
						});
					}									
				},
				user_email: {
					async: true,
					msg: "This email is taken, please use another one."	,
					remote: function(value, attr, model, computed, deferred){
						$.ajax({
							data: {
								action: 'omc_user_check_new_email',
								value: value
							},
							success: function(response){
								if("1" === response.status)
									deferred.resolve();
								else
									deferred.reject();
							},
							error: function(){
								deferred.reject();
							}
						});
					}									
				}
			}
		)
	});

	// Define a View that uses our model
	var registerView = bb.View.extend({
		events: {
			'submit': 'submit',
			'keyup #password': 'updateOnChanged',
			'keyup #email': 'updateOnChanged',
			'keyup #username': 'updateOnChanged'
		},

		initialize: function () {
			// This hooks up the validation
			bb.Validation.bind(this);
			this.listenTo(this.model, 'change', this.validate);
		},
		
		submit: function(e){
			e.preventDefault();
			this.register();
		},
		
		register: function () {
			// Check if the model is valid before saving
			var view = this;
			
			this.model.isValid(true, function(){

				var data = $.extend({}, view.$el.serializeObject(), view.model.toJSON());

				apps.overlay.set('type', 'loading').show();

				view.xhr = $.ajax({
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
		if($('#form-register').length){
			var view = new registerView({
				el: '#form-register',
				model: new registerModel()
			});
		}
	});

})(jQuery, window, Backbone, _, apps, info);