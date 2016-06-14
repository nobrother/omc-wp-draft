var apps = apps || {};

(function($, window, bb, _, apps){
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
		validation: {
			username: [{
				required: true,
				msg: 'We need your username.'
			},{
				minLength: 3,
				msg: 'At least 3 characters long.'
			}],
			password: [{
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
			'submit': function (e) {
				e.preventDefault();
				this.login();
			}
		},

		initialize: function () {
			// This hooks up the validation
			bb.Validation.bind(this);
		},

		login: function () {
			var data = this.$el.serializeObject();
			console.log(this.model.preValidate(data));
			this.model.set(data);
			
			// Check if the model is valid before saving
			// See: http://thedersen.com/projects/backbone-validation/#methods/isvalid
			if(!this.model.preValidate(data)){
				// this.model.save();
				alert('Great Success!');
			}
		}
	});

	$(function () {
		var view = new loginView({
			el: '#form-login',
			model: new loginModel()
		});
	});
})(jQuery, window, Backbone, _, apps);