var apps = apps || {};

(function($, window, doc, bb, _, apps, info){
	// Set uploader
	_.extend(window.wp.Uploader.defaults.multipart_params, {
		'add_terms': 'Avatar|attachment_category'
	});
	
	
	// Define a model with some validation rules
	var editAccountModel = bb.Model.extend({
		defaults:{
			display_name: '',
			description: '',
			avatar: ''
		},
		validation: apps.users.validationRules(['display_name'], false)
	});

	// Define a View that uses our model
	var editAccountView = bb.View.extend({
		events: {
			'submit': 'submit',
			'change': 'updateOnChanged',
			'click #avatar-change': 'openUploaderFrame'
		},

		initialize: function () {
			// This hooks up the validation
			bb.Validation.bind(this);
			
			// Listern to model change
			this.listenTo(this.model, 'change', this.validate);
			
			// Set model for the first time
			this.renderModel(this.model);
			
			// Set uploader
			this.setUploader({
				title: 'Choose your avatar',
				button: {
					text: 'Save'
				},
				library:{
					type: 'image',					
					author: info.uid || 0,
					tax_query: [{
						taxonomy: 'attachment_category',
						field: 'slug',
						terms: 'avatar',
					}]
				},
				multiple: false // set this to true for multiple file selection
			});
		},

		submit: function(e){
			e.preventDefault();
			this.editAccount();
		},
		
		editAccount: function () {
			// Check if the model is valid before saving
			var view = this;
			
			this.model.isValid(true, function(){
				var data = $.extend({}, view.$el.serializeObject(), view.model.toJSON());
				
				apps.overlay.set('type', 'loading').show();
				
				$.ajax({
					data: data,
					success: function(response){
						
						if('1' === response.status)
							apps.overlay.set({type: 'bigText', text: 'Your account is changed!'}).hide();							
						else
							apps.overlay.set({type: 'bigText', text: response.error}).hide(300, 5000);
					}
				});
			});
		},
		
		setUploader: function( options ){
			var uploader = this.uploader = window.wp.media(options),
					view = this;
			
			// EVENT: On select
			uploader.on('select', function(){
				var attachment = uploader.state().get('selection').first().toJSON();
				view.$('#avatar-img').attr('src', attachment.sizes.thumbnail.url);
				view.model.set('avatar', attachment.id);
			});
		},
		
		openUploaderFrame: function(e){
			e.preventDefault();			
			this.uploader.open();
		}
	});

	$(function () {
		// Load the apps if the form exists
		if($('#form-account').length){
			var view = new editAccountView({
				el: '#form-account',
				model: new editAccountModel()
			});
		}
	});

})(jQuery, window, document, Backbone, _, apps, info);