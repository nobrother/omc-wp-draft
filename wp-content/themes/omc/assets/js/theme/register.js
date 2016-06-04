/**
*	Apps js
*/
(function($, global, body){
	var registerApp = global.registerApp = function(){
		var s = {},
				win = $(global),
				doc = $(body),
				container = '',
				nav = '',
				postData = {},
				
				/*
				 * Function to get DOM
				 */
				getDOM = function(){
					container = $('#register-user');
					nav = $('#register-process-flow-nav');
				},
				
				/*
				 * Function to set step status
				 * Trigger 'statusChanged' event if changed
				 */
				setStepStatus = function(stepId, status){
					
					if(!(step = steps[stepId]))
						return false;
					
					if(step.stepStatus !==  status){
						preStatus = step.stepStatus;
						step.stepStatus = status;
						
						// Trigger change event
						container.trigger('statusChanged.register', { step: stepId, status: status, preStatus: preStatus });	
					}
				},
				
				/*
				 * Function to set process lock
				 * Trigger 'locked' event if changed
				 */
				setLock = function(status){
					
					var status = (typeof status === 'undefined') || !!status;
					
					if(typeof locked === 'undefined')
						locked = false;
					
					if(locked !== status){
						preStatus = locked;
						locked = status;
						
						// Trigger change event
						container.trigger('lockChanged.register', { status: status, preStatus: preStatus });	
					}
					
					return locked;
				},
				unlock = function(){
					return setLock(false);
				},
				
				/*
				 * Function reset step lock
				 */
				resetStepsLock = function(excluded){
					
					if(!steps)
						return false;
					
					var excluded = excluded || s.currentStep || '';
					
					if(typeof excluded == 'string' && steps[excluded])
						excluded = steps[excluded] || {id: ''};
					
					for(key in steps){
						
						if(key == excluded.id){
							unlockStep(key);
						} else {
							if(steps[key].stepStatus)
								unlockStep(key);
							else
								lockStep(key);
						}						
					}					
				},
				
				/*
				 * Function lock/unlock step
				 */
				lockStep = function(key, lock){
					
					if(!steps || !key || !(step = steps[key]))
						return false;
					
					var lock = typeof lock === 'undefined' || !!lock;
					
					if(lock){
						step.nav.addClass('disabled');
						step.tab.removeClass('active');
						step.form.find('fieldset.form-input').prop('disabled', true);
					} else {
						step.nav.removeClass('disabled');
						step.form.find('fieldset.form-input').prop('disabled', false);
					}
					
				},
				unlockStep = function(key){
					lockStep(key, false);
				},

				/*
				 * Get next step
				 */
				getNextStepId = function(currentId){
					if(!currentId || !steps)
						return false;

					var c = false;
					for(key in steps){
						if(c)
							return key;

						if(key == currentId)
							c = true;
					}
					return false;
				},
				/*
				 * Get next step
				 */
				getPrevStepId = function(currentId){
					if(!currentId || !steps)
						return false;

					var cache = false;
					for(key in steps){
						if(key == currentId)
							return cache;

						cache = key;
					};
					return false;
				},

				// Form message
				formMessage = function(form, status, message){
					var container = form.find('.form-message');
					if(container.length){
						container.html(
							(['<div class="alert alert-'+status+' alert-dismissible">',
								'<button class="close" data-dismiss="alert">',
								'<i class="fa fa-times"></i>',
								'</button>',
								message,
								'</div>'
							 ].join(''))
						);
					}
				},

				/*
				 * Function to register step
				 * Id step id = html id. ei. step-1
				 * Include default settings
				 * Override with data
				 */
				registerStep = function(id, data){

					if(!id)
						return false;

					var defaultStep = {
						id: id,						
						loadValidator: function(){ 

							var self = this;

							self.form.formValidation(self.validatorSetting)

							// Event: Before validate
								.on('prevalidate.form.fv', function(e){
								//console.log('pre-validate');
								//setLock();
								//return false;
							})

							// Event: Validate error
								.on('err.form.fv', function(e) {
								unlock();
							})

							// Event: Validate success
								.on('success.form.fv', function(e) {
								// Prevent form submission
								e.preventDefault();
								unlock();
							});

							return self.form.data('formValidation');
						},
						validatorSetting: { framework: 'bootstrap' },
						validator: {},
						isValid: function(){
							if(null === this.validator.isValid())
								this.validator.validate();

							var result = this.validator.isValid();

							// Make sure we got the spr result							
							return result;
						},
						stepStatus: false,
						setStatus: function(status){
							setStepStatus(id, status);

							// Reset next steps as false
							var next = this;
							while(next = next.next())
								setStepStatus(next.id, false);

						},
						submit: function(e){ },
						next: function(){
							if(next = getNextStepId(this.id))
								return steps[next];
							return false;
						},
						prev: function(){
							if(prev = getPrevStepId(this.id))
								return steps[prev];
							return false;
						},

						// Submit deferred
						startSubmit: function(){

							var self = this;

							self.submitDfd = new $.Deferred();

							// Submit deferred
							self.submitDfd.done(function(data) {
								//console.log(data);
								if(data.message)
									formMessage(self.form, 'success', data.message);
							})
								.fail(function(data) {
								//console.log(data);
								if(data.message)
									formMessage(self.form, 'warning', data.message);
							})
								.always(function(data) {
								//console.log(data);
							});
						},
						submitDone: function(data){
							this.submitDfd.resolve(data);
						},
						submitFail: function(data){
							this.submitDfd.reject(data);
						},



						// Event: When DOM ready
						init: function(){

							var self = this;

							// Get DOM element
							self.nav = nav.find('a[href="#'+id+'"]').parent();
							self.tab = $('#'+id);
							self.form = $('#form-'+id);
							self.nextBtn = self.form.find('.next-step');

							// Load Validator
							self.validator = self.loadValidator();

							/*
							 * EVENT
							 */
							// Next button
							self.nextBtn.on('click', function(e){
								e.preventDefault();

								// Open tab
								if(next = self.next())
									next.nav.find('a').tab('show');
							})

							// Submit
							self.form.on('submit', function(e){

								e.preventDefault();

								// Validate
								if(!self.isValid()){
									self.setStatus(false);
									return false;
								}

								// Process is lockdown
								if(s.isLocked())
									return false;

								self.startSubmit();
								self.submit(e);

							});
						}
					}

					if(!steps)
						steps = {};

					steps[id] = $.extend({}, defaultStep, data);

					return true;
				},
				
				steps = {};
		
		// Public: isLocked?
		s.isLocked = function(){
			return locked;
		};
		
		/*
		 * Register steps
		 */
		// Step 1
		registerStep('step-1', {
			
			// Load settings
			validatorSetting: {
				framework: 'bootstrap',
				fields: {
					user_ic: {
						validators: {
							notEmpty: {
								message: 'The IC is required'
							}
						}
					}
				}
			},
			
			// Check SPR
			submit: function(e){
				
				var self = this,
						submitBtn = self.form.find('.btn-verify');
				
				// Data
				var data = $.extend({}, self.form.serializeObject(), {
					action: 'at_user_register_check_spr'
				});
				
				// Request
				setLock();	// Set lock
				
				// Submit button
				submitBtn.state('verifying');
				
				$.post(info.ajaxurl, data, function(response){
					
					console.log(response);
					
					unlock();
					
					if(response.error){
						self.setStatus(false);
						
						// Update post data
						postData.sprResult = {};
						
						// Submit button
						submitBtn.state('default');
						
						// Set submit fail
						self.submitFail({
							message: response.error
						});
						
						// Reset step lock
						resetStepsLock(self);
					} else {
						self.setStatus(true);
						submitBtn.state('ok');
						
						// Update post data
						postData.sprResult = response.result;
						
						$('#spr-result').html(response.html);
						
						// Set submit fail
						self.submitDone({
							message: 'Ok good to go!'
						});
						
						// Reset step lock
						resetStepsLock(self.next());
					}
				})
			}
		});
		
		// Step 2
		registerStep('step-2', {
			
			// Load settings
			validatorSetting: {
				framework: 'bootstrap',
				fields: {
					user_ic_name: {
						validators: {
							notEmpty: {
								message: 'Your name is required'
							}				
						}
					}
				}
			},
			
			// Submit
			submit: function(e){
				
				var self = this,
						formdata = self.form.serializeObject(),
						submitBtn = self.form.find('.btn-verify');
				
				submitBtn.state('verifying');
				
				// Check IC Name
				if(formdata.user_ic_name){
					var inputValue = $.trim(formdata.user_ic_name.toLowerCase()),
							compareValue = $.trim(postData.sprResult.name.toLowerCase());
					
					if(inputValue == compareValue){
						self.setStatus(true);
						submitBtn.state('ok');
						// Reset step lock
						resetStepsLock(self.next());
					} else {
						self.setStatus(false);
						submitBtn.state('error');
						// Reset step lock
						resetStepsLock(self);
					}						
				}				
			}
		});
		
		// Step 3
		registerStep('step-3', {
			
			// Load settings
			validatorSetting: {
				framework: 'bootstrap',
				fields: {
					user_gender: {
						validators: {
							notEmpty: {
								message: 'Please choose your gender.'
							}				
						}
					}
				}
			},
			
			// Submit
			submit: function(e){
				
				var self = this,
						formdata = self.form.serializeObject(),
						submitBtn = self.form.find('.btn-verify');
				
				submitBtn.state('verifying');
				
				// Check gender
				if(formdata.user_gender){
					var inputValue = $.trim(formdata.user_gender.toLowerCase()),
							compareValue = $.trim(postData.sprResult.gender.toLowerCase());
				
					if(inputValue == compareValue){
						self.setStatus(true);
						submitBtn.state('ok');
						// Reset step lock
						resetStepsLock(self.next());
					} else {
						self.setStatus(false);
						submitBtn.state('error');
						// Reset step lock
						resetStepsLock(self);
					}						
				}				
			}
		});
		
		// Step 4
		registerStep('step-4', {
			
			// Load settings
			validatorSetting: {
				framework: 'bootstrap',
				fields: {
					user_email: {
						validators: {
							notEmpty: {
								message: 'Please provide your email.'
							},
							emailAddress: {
								message: 'Please provide a valid email address.'
							}
						}
					},
					user_pass: {
						validators: {
							notEmpty: {
								message: 'Please provide your password.'
							},
							stringLength: {
								message: 'Password must be 6 - 12 characters only.',
								min: 6,
								max: 12
							}
						}
					},
					register_submit: {
						validators: {
							callback: {
								message: 'You must complete the previous steps',
								callback: function(value, validator, field) {
									
									var stepsComplete = true,
											stepId = field.data('step');									
									
									for(var key in steps){
										if(key == stepId)
											continue;

										if(!steps[key].stepStatus){
											stepsComplete = false;
											break;
										}
									}

									return stepsComplete;
								}
							}
						}
					}
				}
			},
			
			// Submit
			submit: function(e){
				
				var self = this,
						submitBtn = self.form.find('.btn-primary');
				
				// Data
				var data = $.extend({}, postData, self.form.serializeObject(), {
					action: 'at_user_register_user'
				});
				
				// Request
				setLock();	// Set lock
				
				// Submit button
				submitBtn.state('submiting');
				
				$.post(info.ajaxurl, data, function(response){
					
					console.log(response);
					
					unlock();
					
					if(response.error){
												
						// Submit button
						submitBtn.state('default');
						
						self.submitFail({
							message: response.error
						});
						
						// Reset step lock
						resetStepsLock(self);
					} else {
						self.setStatus(true);
						submitBtn.state('ok');
						
						self.submitDone({
							message: 'Yeah! Success!'
						});
						
						// Reset step lock
						resetStepsLock(self);
					}
				})
			}
		});
		
		// Load all the steps
		s.init = function(){
			
			if(!steps)
				return;
			
			var i = 0;
			
			// Get important DOM
			getDOM();
			
			// Loop through steps to initiate
			for(key in steps){
				
				var step = steps[key];

				// Current step
				if(i === 0)
					s.currentStep = key;

				// Initiate step
				step.init();

				i++;
			}
			
			/*
			 * EVENT
			 */
			// Event: container lock down
			container.on('lockChanged.register', function(e, data){
				// Lock
				if(data.status){

					// Container
					container
						.addClass('disabled')
						.find('fieldset.form-input').prop('disabled', true);

					// Nav / Tab
					nav
						.addClass('disabled')

					// Disabled interaction
						.on('click.locked focus.locked mouseover.locked touchstart.locked touchend.locked', 'a[data-toggle=tab]', function(e){
						e.preventDefault();
						return false;				
					})	
				}

				// Unlock
				else {

					// Container
					container
						.removeClass('disabled')
						.find('fieldset.form-input').prop('disabled', false);

					// Nav / Tab
					nav
						.removeClass('disabled')
						.off('.locked');			
				}
			})

			// Event: step lock down
				.on('statusChanged.register', function(e, data){
				if(data.step && (step = steps[data.step])){
					step.form.find('fieldset.form-nav').prop('disabled', !data.status);
				}
			});
			
			// Reset step lock
			resetStepsLock();
			
		}

		return s;
	}();
	
	
})(jQuery, window, document)