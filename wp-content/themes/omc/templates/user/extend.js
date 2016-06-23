/*
 * General settings for user apps
 */
var apps = apps || {};

(function($, window, bb, apps, _){
	
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

	_.extend(bb.Validation.validators, {
		/**
     * Validator to check if a inserted username is already existing
     *
     * @param {string} value Input value
     * @param {string} attr Attribute name
     * @param {string} customValue
     * @param {Backbone.Model} model
     * @param {object} deferred Promise to resolve or reject
     */
		remote: function (value, attr, customValue, model, computed, deferred) {
			if('function' === typeof customValue)
				customValue(value, attr, model, computed, deferred);
			else
				deferred.reject();
		}
	});
	
	
	apps.users = {
		
		// Validation Rules
		validationRules: (function(){
			var rules = {
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
				user_email: [{
					required: true,
					msg: 'We need your email as well.'
				},{
					pattern: 'email',
					msg: 'We need your real email of course.'
				}],			
				user_pass: [{
					required: true,
					msg: 'We need your password as well.'
				},{
					minLength: 8,
					msg: 'At least 8 characters long.'
				}]
			}

			return function(attributes, extend){
				var output = attributes && $.isArray(attributes) && _.pick(rules, attributes) || {};
				if(extend){
					$.each(extend, function(key, value){
						if('undefined' === typeof output[key])
							output[key] = value;
						else if($.isArray(output[key])){
							if($.isArray(value))
								$.each(value, function(index, value){
									output[key].push(value);
								})
							else
								output[key].push(value);
						}
						else
							output[key] = [value];
					});
				}
				
				return output;
			}
		})()
	};

})(jQuery, window, Backbone, apps, _);