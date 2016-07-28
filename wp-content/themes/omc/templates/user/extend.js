/*
 * General settings for user apps
 */
var apps = apps || {};

(function($, window, bb, apps, _){
	
	// Ajax setup
	$.ajaxSetup({
		url: window.info.ajaxurl,
		type: 'POST',
		beforeSend: function(xhr, settings){
			settings.data += '&'+$.param({url_id: window.info.url_id});
			return true;
		},
		success: function(response){
			console.log(response);
		},
		error: function(xhr, status, error){
			console.log(status + ': ' + error);
		}
	});	
	
	// General Backbone extend
	_.extend(bb.View.prototype, {
		
		// Set current view data into model
		renderModel: function(model){
			_.each(
				model.defaults, 
				function(value, key){
					this.updateOnChanged({target: this.$('[name='+key+']')});
				}, 
				this);
		},
		
		// Update Model when view change
		updateOnChanged: function(e){
			var input = $(e.target),
					name = input.attr('name') || '';
			
			if(name)
				this.model.set(name, input.val());
		},
		
		// Validate model
		validate: function(model){
			if(model.changed){
				$.each(model.changed, function(key, value){
					model.isValid(key, function(){
						//console.log(this);
					}, function(model){
						//console.log(model);
					});
				})
			}
		},
		
		/*
		 * Helper function to declare delay
		 * HOW TO USE:
		 * var delay = this.inputDelay('123');
		 * delay(function(){ ... }, 300);
		 */
		inputDelay: function(key){
			if(!key) return false;
			
			this.inputDelayData = this.inputDelayData || {};			
			
			if(this.inputDelayData && this.inputDelayData[key])
				return this.inputDelayData[key];
			
			return this.inputDelayData[key] = window.delay();
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
					minLength: 6,
					msg: 'At least 6 characters long.'
				},{
					maxLength: 60,
					msg: 'Cannot more than 60 characters.'
				}],
				old_password: [{
					required: true,
					msg: 'We need your current password.'
				}],
				display_name: [{
					required: true,
					msg: 'Your name cannot be empty.'
				}]
			};

			return function(attributes, extend){
				var attributes = _.isString(attributes) && [attributes] || $.isArray(attributes) && attributes || false,
						output = attributes && $.isArray(attributes) && _.pick(rules, attributes) || rules;
				
				// Clone
				output = JSON.parse(JSON.stringify(output));

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
	}

})(jQuery, window, Backbone, apps, _);