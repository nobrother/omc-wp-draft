var apps = apps || {};

(function($, window, bb, _, apps, info){
	// Ajax setup
	$.ajaxSetup({
		url: info.ajaxurl,
		type: 'POST',
		success: function(responce){
			console.log(responce);
		},
		error: function(xhr, status, error){
			console.log(status + ': ' + error);
		}
	});	
	
	// Validation setup
	_.extend(bb.Validation.callbacks, {
		valid: function (view, attr, selector) {
			var $el = view.$('[name=' + attr + ']'), 
					$group = $el.closest('.form-group');

			$group.removeClass('has-error');
			$group.find('.help-block').html('').addClass('hidden');
		},
		invalid: function (view, attr, error, selector) {
			var $el = view.$('[name=' + attr + ']'), 
					$group = $el.closest('.form-group');

			$group.addClass('has-error');
			$group.find('.help-block').html(error).removeClass('hidden');
		}
	});

	// Define a model with some validation rules
	var loginModel = bb.Model.extend({
		defaults:{
			user_login: '',
			user_password: ''
		},
		validation: {
			user_login: [{
				required: true,
				msg: 'We need your username.'
			},{
				minLength: 3,
				msg: 'At least 3 characters long.'
			},{
				maxLength: 60,
				msg: 'Cannot more than 60 characters.'
			}],
			user_password: [{
				required: true,
				msg: 'We need your password as well.'
			},{
				minLength: 8,
				msg: 'At least 8 characters long.'
			}]
		}
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
			if(this.model.isValid(true)){
				var data = $.extend({}, this.$el.serializeObject(), this.model.toJSON());
				$.ajax({
					data: data					
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
		}
	});

	$(function () {
		var view = new loginView({
			el: '#form-login',
			model: new loginModel()
		});
	});

})(jQuery, window, Backbone, _, apps, info);