/**

*	AJAX js

* Javascript for every page in current site

*/

var ajaxurl = info.ajaxurl;
var excelMIME = [
  'application/vnd.ms-excel', // xls
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
  'application/vnd.ms-excel.sheet.macroEnabled.12' // xlsm
];

(function($){
  $(function(){    
    
    /*******************************
   	* EDIT BRANCHES
   	*******************************/
    $('#form-analysis-branch').on('submit', function(e){
      
      e.preventDefault();
      
      var self = $(this);
      
      var data = {
        action		: 'mv_edit_branches',
        post_data	: self.serialize()
      }
      
      showLoading();
      
      $.post(ajaxurl, data, function(response){
        
        hideLoading();
        
				trace(response);
      });
    });

    /*******************************
   	* ANALYSIS CHECK SYSTEM
   	*******************************/
    $('#analysis-chk-system').on('click', function(e){   
      
      if(!analysis){
        alert('Hey! No cheating!');
        return false;
      }
			
      e.preventDefault();
      
      // Update analysis form
      analysis.beforeSubmit();
      
    	var data = {
        action		: 'mv_analysis_check_system',
        post_data	: analysis.el.serialize()
      }
      
      showLoading("Checking")
      
      $.post(ajaxurl, data, function(response){
        
        trace(response);
        
        // Update value
        updateValue(response);
        updateChosen();
        
        hideLoading()
        
      })
    })
    
    /*******************************
		 * CHECK PPS
		 *******************************/
    $('form.check-pps', '#tb-pps-for-check').on('submit', function(e){

      e.preventDefault();

      var form = $(this).closest('form');
      var progressId = randomString();

      var data = {
        action		: 'mv_check_pps',
        progress_id : progressId,
        post_data	: form.serialize()
      }
			
      // Remove previous attention class
      $('#tb-pps-for-check').find('tr').removeClass('attention');
      
      var xhr = $.post(ajaxurl, data, function(response){

        trace(response);
        if(response.error){
          //alert(response.error);
          }else{
          response.updatevalue && updateValue(response.updatevalue);
          //window.location.replace(response.redirect);
        }

      })

      progress.set('title', 'Checking TM Portal').getProgress(progressId, xhr);

    })
    
    /*******************************
   	* IMPORT LATEST APPV CHECK
   	*******************************/
    $('#confirm-appv-import-check').on('click', function(e){
      
      if(!analysis){
        alert('Hey! No cheating!');
        return false;
      }
      
      // Validate
      if(!analysis.el[0].checkValidity())
        return true;
      e.preventDefault();

      // Update analysis form
      analysis.beforeSubmit();
      
      var data = {
        action		: 'mv_import_latest_appv_check',
        post_data	: analysis.el.serialize()
      }
      
      showLoading('Importing');
      
      $.post(ajaxurl, data, function(response){
        trace(response);
        
        if(response.acc)
          updateValue(response.acc);
        
        if(response.ani)
          updateValue(response.ani);
       	
        updateDatetime();
        
        hideLoading();
      })
    });
    
    /*******************************
   	* UPLOAD USAGE
   	*******************************/
    var excelMIME = [
      'application/vnd.ms-excel', // xls
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
      'application/vnd.ms-excel.sheet.macroEnabled.12' // xlsm
    ];
    
    
    /*******************************
   	* CREATE NEWS
   	*******************************/
		$('#newsfeed .newsfeed-input-wrap form').on('submit', function(e){
    	
      e.preventDefault();
      var form = $(this);
      var data = {
        action		: 'mv_create_news',
        post_data	: form.serialize()
      }
      
      showLoading()
      
      $.post(ajaxurl, data, function(response){
        
        trace(response);
        
        hideLoading();     
        
        if(!response.error){
          $('#newsfeed-item-template').clone().removeAttr('id').html(response.log).hide().prependTo('#newsfeed .list-group').fadeIn(500);
          resetForm(form);
        }
        
      })
    }).find('.newsfeed-input-text textarea').on('keydown', function(e){

      if(e.which == 13){
        $(this).closest('form').find('button[type=submit]').click();
        return false; 
      }

    });
    
    /*******************************
   	* NEWSFEED
   	*******************************/
    var newsfeed = function(){

      var self = newsfeed;
      self.loading = false;
      self.eof = false;
      self.el = $('#newsfeed .list-group');

      // Unlimited load
      self.el.on('scroll', function(e){
        if( !self.loading && !self.eof && ( $(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight - 500 ) ){
          var data = {
            action		: 'mv_load_news',
            post_data	: self.el.find('form').serialize()
          }
          
          // Stop further loading
          self.loading = true;
          $.post(ajaxurl, data, function(response){
            
            trace(response);
            
            if(response.logs){
              var template = $('#newsfeed-item-template').clone().removeAttr('id');
            	for(x in response.logs){
              	template.clone().html(response.logs[x]).hide().appendTo('#newsfeed .list-group').fadeIn(500);
              }
            }
            
            if(response.last_date)
              self.el.find('form input[name=last_date]').val(response.last_date);
            
            if(response.last_log_id)
              self.el.find('form input[name=last_log_id]').val(response.last_log_id);
            
            if(response.eof)
              self.eof = true;
            
            self.el.perfectScrollbar('update');
            
            self.loading = false;
          })
        }
      }).trigger('scroll');

    }

    newsfeed();

  });
  
  
  
})(jQuery);

/*******************************
 * PROGRESS BAR
 *******************************/    
var progress = function(options, selector){
	var $ = jQuery;
  var selector = selector || '#progress-bar';
  
  var options = options || {};
  var defaultOptions = {
  	title: 'Processing...',
    description: 'Hmm, this might take a while... Maybe check your facebook and come back :P',
    attemptLimit: 10
  }
  
  // Overide options
  // Please define init() and beforeSubmit in options
  $.extend(progress, defaultOptions, options);
  var self = progress;
  
  self.el = $(selector);
  self.progressAttemptCounter = 0;
  
  // Set options
  self.set = function(param1, param2){
    var options = {};
  	if(typeof param1 == 'object' && !isArray(param1)){
    	options = param1;
    } else if(typeof param1 !== 'undefined' && typeof param2 !== 'undefined') {
    	options[param1] = param2;
    }
    if(options){
    	$.extend(self, options);
      $.extend(progress, options);
    }
    return self;
  };
  
  // Show Progress
  self.showProgress = function(){
    self.el.find('.progress-title').html(self.title);
    self.el.find('.progress-description').html(self.description);
    self.el.modal({
      backdrop: 'static',
      keyboard: true
    })
    return self;
  }
  
  // Reset Progress
  self.resetProgress = function(){
    self.el.find('.progress-title').html('');
    self.el.find('.progress-description').html('');
    self.el.find('.progress-value').html('0%');
    self.el.find('.progress-bar').css('width', '0');
    return self;
  }
  
  // Hide Progress
  self.hideProgress = function(){
    self.el.modal('hide');
    return self;
  };
  
  // Get Progress
  self.getProgress = function(progressId, xhr){

    var data = {
      action : 'mv_get_progress',
      progress_id : progressId,
      post_data	: { progress_id : progressId }
    }

    self.showProgress();	

    $.post(ajaxurl, data, function(response){
      trace(response);
      if(response.not_exists){

        if(self.progressAttemptCounter < self.attemptLimit){
          setTimeout(function(){
            self.getProgress(progressId);
          }, 1000);
          self.progressAttemptCounter++;
        } else {
          self.hideProgress();
          self.resetProgress();
          typeof xhr !== 'undefined' && xhr.abort();
          alert('Fail to process it... Please try again');
        }

        return;
      } else if(!response.complete && !response.stop){			

        self.progressAttemptCounter = 0;
        self.getProgress(progressId);

        percent = Math.round(response.progress*100) + '%';

        self.el.find('.progress-bar').css('width', percent);
        self.el.find('.progress-value').html(percent);

      } else {
        self.progressAttemptCounter = 0;
        self.el.find('.progress-bar').css('width', '100%');
        self.el.find('.progress-value').html('100%');

        setTimeout(function(){

          self.hideProgress();
          self.resetProgress();

        }, 700);

        // Output
        if(response.info.alert){
          alert(response.info.alert);
        }
      }
    })
    return self;
  }
}


//trace('cache test!');