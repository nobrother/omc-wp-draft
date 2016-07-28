var apps = apps || {};

(info && 'user_change_password' === info.url_id) && (function($, window, bb, _, apps, info){
	
	// Define a model with some validation rules
	var passwordModel = bb.Model.extend({
		defaults:{
			old_password: '',
			user_pass: ''
		},
		validation: apps.users.validationRules(['old_password', 'user_pass'], false)
	});

	// Define a View that uses our model
	var passwordView = bb.View.extend({
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
			this.changePassword();
		},
		
		changePassword: function () {
			// Check if the model is valid before saving
			var view = this;
			
			this.model.isValid(true, function(){
				var data = $.extend({}, view.$el.serializeObject(), view.model.toJSON());
				
				apps.overlay.set('type', 'loading').show();
				
				$.ajax({
					data: data,
					success: function(response){
						console.log(response);
						if('1' === response.status)
							apps.overlay.set({type: 'bigText', text: 'Your password is changed!'}).hide();							
						else
							apps.overlay.set({type: 'bigText', text: response.error}).hide(300, 5000);
					}
				});
			});
		}
	});

	$(function () {
		// Load the apps if the form exists
		if($('#form-password').length){
			var view = new passwordView({
				el: '#form-password',
				model: new passwordModel()
			});
		}
	});

})(jQuery, window, Backbone, _, apps, info);