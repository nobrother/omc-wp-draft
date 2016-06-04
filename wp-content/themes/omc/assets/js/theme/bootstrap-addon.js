








/*******************************
 * MODAL Addon
 *******************************/
(function($){
  $(function(){
    // Trace which element trigger modal
    $('body').on('click', '[data-toggle=modal]', function(){
      var self = $(this);
      setTimeout(function(){
        // Check the modal is shown
        if($(self.data('target')).data('bs.modal').isShown)
          $(self.data('target')).data('bs.modal').lastModalTrigger = self;
      }, 100)
    })
  })
})(jQuery);


/*******************************
 * SCROLL
 *******************************/
(function($){	 
  $(function(){
    // Prevent scroll bubble
    $('.scroll').on('mousewheel', function(e, d){
      if(d === -1 && ($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight) && this.scrollHeight > $(this).innerHeight() ){
        e.preventDefault();
      }
    });
  })
})(jQuery); 


/*******************************
 * CHECKBOX BEHAVIOUR
 *******************************/
(function($){	 
  $(function(){
    $(document).on('change', 'input[type=checkbox][data-toggle="checkbox-group"]', function(e){
			
      var selector = $(this).data('target');
			
      if(selector){
        var checked = this.checked;        
        $('input[type=checkbox]').filter(selector).prop('checked', checked);
      }

    })    
  });
})(jQuery); 


/*******************************
 * Dismiss API
 *******************************/
(function($){	 
  $(function(){
    $(document).on('click', '[data-action=dismiss]', function(e){

      e.preventDefault();
      e.stopPropagation();

      var selector = $(this).data('closest-target');

      if(!selector)
        return false;

      var parent = $(this).closest(selector);
      if(!parent.length)
        return false;

      var container = $($(this).data('container'));
      if(!container.length)
        container = parent.parent() || $('body');

      var animate = $(this).data('animate');
      var speed = $(this).data('animate-speed') || 200;

      function removeElement(){
        $(this).detach().trigger('dismiss.bs.element').remove();
        container.trigger('afterDismiss.bs.element');
      }

      if(animate){
        parent.stop(true, true);
        switch(animate){
          case 'fade':
          default:
            $.support.transition ?
              parent
            .one('bsTransitionEnd', removeElement)
            .emulateTransitionEnd(speed) :
            removeElement()
        }

      }

    })
  });
})(jQuery);


/*******************************
 * CHOSEN
 *******************************/

/**
 * PRACTISE:
 * 1.	Use loadChosen() everytime when chosen element insert in DOM
 * 2.	Use maybeInitiateLazyChosen() to initial lazy chosen, but element must be loaded by loadChosen() first
 */

// EVENT
(function($){
	$(function(){    
    // Handle readonly chosen
    $('.chosen-zone', 'body').on('chosen:updated', "select", function() {
      
      var select = $(this);
      
      if(!select.data('chosen'))
        return;
      
      if (select.attr('readonly')) {
        var wasDisabled = select.is(':disabled');

        select.attr('disabled', 'disabled');
        select.data('chosen').search_field_disabled();

        if (wasDisabled) {
          select.attr('disabled', 'disabled');
        } else {
          select.removeAttr('disabled');
        }
      }
    });
  })
})(jQuery)
  
// Load Chosen
function loadChosen(){
  var $ = jQuery, 
      zone = $(".chosen-zone", "#wrapper"), 
      chosen = zone.find(".chosen-select"),
      lazyChosen = zone.find(".chosen-select-lazy"),
      chosenAjax = zone.find(".chosen-select-ajax"),
      lazyChosenAjax = zone.find(".chosen-select-ajax-lazy");
  
  // Pre select the first choice if it is mandatory field(s)
  chosen.add(lazyChosen).add(chosenAjax).add(lazyChosenAjax).filter('[required]').each(function(){    
    if(!$(this).val() && $(this).children('option').get(0))
       $(this).children('option').get(0).selected = true;
  })
  
  // Disable tabindex
  chosen.add(lazyChosen).add(chosenAjax).add(lazyChosenAjax).attr('tabindex', -1);
  
  // Load regular chosen
  chosen.filter('[required]').not('.combo').chosen({width: '100%', search_contains: true });
  chosen.filter('[required]').filter('.combo').chosen({width: '100%', search_contains: true, no_results_text: 'Press Ctrl + Enter to add'}); 
  chosen.not('[required]').not('.combo').chosen({width: '100%', allow_single_deselect: true, search_contains: true });
  chosen.not('[required]').filter('.combo').chosen({width: '100%', allow_single_deselect: true, search_contains: true, no_results_text: 'Press Ctrl + Enter to add'});
  chosen.filter(".combo").each(function(){
    var select = $(this);
    select.siblings('.chosen-container').find('input[type=text]').on('keydown', function(e){
      if(e.which === 13 && e.ctrlKey){
        select.val(this.value);
        $('<option selected>' + this.value + '</option>').prependTo(select);
        select.trigger("chosen:updated");
      }
    })
  })
  
  // Make html Options
  // Can't support search
  chosen.filter('.html-options').each(function(){
    var select = $(this);
    select.on('change', function(){
      var target = select.siblings('.chosen-container').find('.chosen-single span');
      var text = target.html().replace(/[/g,'<').replace(/]/g,'>');
      target.html(text);
    }).on('chosen:showing_dropdown', function(){
      select.siblings('.chosen-container').find('.chosen-results li').each(function(){
        var text = $(this).html().replace(/[/g,'<').replace(/]/g,'>');
        trace(text);
        $(this).html(text);
      })
    }).trigger('change').siblings('.chosen-container').find('.chosen-search').hide();
  });

  chosen.removeClass('chosen-select').addClass('chosen');

  // Load Chosen Ajax
  var chosenAjaxData = {    
    accList: { selector: 'select.acc-list', action: 'mv_chosen_get_acc_list', postdata: 'acc_list' },
    coList: { selector: 'select.company-list', action: 'mv_chosen_get_company_list', postdata: 'company_list' }  
  };

  for(x in chosenAjaxData){
    (function(){
      var thisItem = chosenAjaxData[x];
      var self = chosenAjax.filter(thisItem.selector);
      self.removeClass('chosen-select-ajax').addClass('chosen chosen-select-ajax-initiated').ajaxChosen({
        afterTypeDelay: 300,
        minTermLength: 1,
        type: 'POST',
        url: ajaxurl,
        data: {
          action		: thisItem.action,
          post_data	: thisItem.postdata
        }     
      }, function (data) {
        
        updateList(self.not(this.element), data, 'value', 'text', false, false) 
        
        return data;
      },{
        width: '100%',
        search_contains: true 
      });      
    })();    
  }

  // Load lazy chosen
  $(window).off('.lazychosen').on('resize.lazychosen, scroll.lazychosen, maybeinitiate.lazychosen', function(){
    zone.find(".chosen-select-lazy").each(function(){
      var select = $(this);
      /**
       * select.visible(True)
       * True because it will return true if any part of the element is shown
       * but beware to the performance drop
       */
      if(select.visible()){        
        select.filter('[required]').not('.combo').chosen({width: '100%', search_contains: true });
        select.filter('[required]').filter('.combo').chosen({width: '100%', search_contains: true, no_results_text: 'Press Ctrl + Enter to add'}); 
        select.not('[required]').not('.combo').chosen({width: '100%', allow_single_deselect: true, search_contains: true });
        select.not('[required]').filter('.combo').chosen({width: '100%', allow_single_deselect: true, search_contains: true, no_results_text: 'Press Ctrl + Enter to add'});
        select.trigger("chosen:updated")
        if(select.is('.combo')){
          select.siblings('.chosen-container').find('input[type=text]').on('keydown', function(e){
            if(e.which === 13 && e.ctrlKey){
              select.val(this.value);
              $('<option selected>' + this.value + '</option>').prependTo(select);
              select.trigger("chosen:updated");
            }
          })
        }
        select.removeClass('chosen-select-lazy').addClass('chosen');
      }
    });
    
    zone.find(".chosen-select-ajax-lazy").each(function(){
      var select = $(this);
      if(select.visible()){
        for(x in chosenAjaxData){
          if(select.is(chosenAjaxData[x].selector)){
            (function(){
              var selector = chosenAjaxData[x].selector;
              select.removeClass('chosen-select-ajax-lazy').addClass('chosen chosen-select-ajax-initiated')
                .ajaxChosen({
                afterTypeDelay: 300,
                minTermLength: 1,
                type: 'POST',
                url: ajaxurl,
                data: {
                  action		: chosenAjaxData[x].action,
                  post_data	: chosenAjaxData[x].postdata
                }
              }, function (data) {
                 updateList($(selector).not(this.element), data, 'value', 'text', false, false)
                return data;
              },{
                width: '100%',
                search_contains: true 
              });
              select.trigger("chosen:updated");
            })();
            
            break;
          }
        }
      }
    })
  })
  
  maybeInitiateLazyChosen();
  
  
}

/*******************************
 * SELECT HELPER
 *******************************/
(function($){
  
  $(function(){
  	$('body').on('click', '.select-helper', function(e){
    	e.preventDefault();
      e.stopPropagation();
      
      var data = $(this).data();
      data.target = data.target || $(this).attr('href');
      
      if(data.toggle && data.target){
        
      	$(data.target).find('option').each(function(){
          
        	if(data.toggle == 'select-all')
          	this.selected = true;
          else if(data.toggle == 'deselect-all')
            this.selected = false;
        })
        $(data.target).trigger("chosen:updated");
      }
    });
  })  
})(jQuery)


/**
 * Manually initiate lazy chosen initiate if visible
 * The element must be load first by using function loadChosen()
 */
function maybeInitiateLazyChosen(){
  jQuery(window).trigger('maybeinitiate.lazychosen')
}

// Update chosen
function updateChosen(){
  jQuery("select", ".chosen-zone").trigger("chosen:updated")
}


/*******************************
 * DATETIME INPUT
 *******************************/
// Load Datetime
function loadDatetime(){
  var $ = jQuery, 
      defaultOptions = {
        showMeridian: true,
        todayBtn: true,
        forceParse: false,
        autoclose: true
      },
      monthpickerOptions = $.extend({}, defaultOptions, {
        format: 'M yyyy',
        startView: 'year',
        minView: 'year',
        maxView: 'decade',
        linkField: ".monthpicker-output",
        linkFormat: "yyyymm"
      }),
      datetimepickerOptions = $.extend({}, defaultOptions, {
        format: 'd M yyyy, H:ii p',
        //format: 'd M yyyy',
        startView: 'month',
        linkField: ".datetimepicker-output",
        linkFormat: "yyyy-mm-dd hh:ii:ss"
      }),
      datepickerOptions = $.extend({}, defaultOptions, {
        format: 'd M yyyy',
        startView: 'month',
        minView: 'month',
        maxView: 'decade',
        linkField: ".datepicker-output",
        linkFormat: "yyyy-mm-dd"
      });
	
  // Initial
  $(".monthpicker", "#wrapper").removeClass('monthpicker').addClass('monthpicker-initiated').datetimepicker(monthpickerOptions)  
  $(".datetimepicker", "#wrapper").removeClass('datetimepicker').addClass('datetimepicker-initiated').datetimepicker(datetimepickerOptions)  
  $(".datepicker", "#wrapper").removeClass('datepicker').addClass('datepicker-initiated').datetimepicker(datepickerOptions)
  
  // Load lazy datetime
  $(window).off('.lazydatetime').on('load.lazydatetime, resize.lazydatetime, scroll.lazydatetime, maybeinitiate.lazydatetime', function(){
    $(".monthpicker-lazy").each(function(){
      var self = $(this);
      /**
       * .visible(True)
       * True, it will return true if any part of the element is shown
       * but beware to the performance drop
       */
      if(self.visible())
        self.removeClass('monthpicker-lazy').addClass('monthpicker-initiated').datetimepicker(monthpickerOptions);
    })
    $(".datetimepicker-lazy", "#wrapper").each(function(){
      var self = $(this);
      if(self.visible())
        self.removeClass('datetimepicker-lazy').addClass('datetimepicker-initiated').datetimepicker(datetimepickerOptions);
    })
    $(".datepicker-lazy", "#wrapper").each(function(){
      var self = $(this);
      if(self.visible())
        self.removeClass('datepicker-lazy').addClass('datepicker-initiated').datetimepicker(datepickerOptions);
    })
  })

  maybeInitiateLazyDatetime();
}

// Update chosen
function updateDatetime(){
  jQuery(".monthpicker-initiated, .datetimepicker-initiated, .datepicker-initiated", "#wrapper").each(function(){
  	if(jQuery(this).data('datetimepicker'))
      jQuery(this).data('datetimepicker').update();
  })
}

/**
 * Manually initiate lazy datetime initiate if visible
 * The element must be load first by using function loadDatetime()
 */
function maybeInitiateLazyDatetime(){
  jQuery(window).trigger('maybeinitiate.lazydatetime');
  trace('lazy');
}

/*******************************
 * DATA TABLE
 *******************************/
var defaultDataTableSettings = {
	dom: 'frt<"table-nav"i<"paging-wrap"lp>>',
  language: {
    lengthMenu: '<span class="filter-label">View by</span> _MENU_',
    zeroRecords: "Sorry, we can’t find anything related. Maybe try some other words ?",
    emptyTable: "NOTHING here yet ........",
    info: '<span class="start-end">_START_ - _END_</span> of _TOTAL_',
    infoEmpty: '<span class="start-end">0</span> of 0',
    infoFiltered: "",
    paginate: {
    	previous: '<i class="fa fa-angle-left"></i>',
      next: '<i class="fa fa-angle-right"></i>'
    }
  }
}

var simpleDataTableSettings = jQuery.extend(true, {}, defaultDataTableSettings, { dom: 't' })
var paginatedDataTableSettings = jQuery.extend(true, {}, defaultDataTableSettings, { dom: 'rt<"table-nav"i<"paging-wrap"lp>>' })
var fullDataTableSettings = jQuery.extend(true, {}, defaultDataTableSettings, { dom: 'frt<"table-nav"i<"paging-wrap"lp>>' })

//trace(simpleDataTableSettings);

/*******************************
 * FUNCTIONS
 *******************************/

/**
* Update dropdown list
*
* selector 		= selector of input/DOM element which refer to the targeted <select>
*							ex. '.chosen-select'
*
* data 				= the array/object content the list information
*             ex. 
*							[
*	            	{ id: 1, name: acc1, extra: xxx },
*	            	{ id: 2, name: acc2, extra: yyy },
*	            	{ id: 4, name: acc4, extra: zzz }
*							]
*							OR
*							{  
*	            	acc1: { id: 1, name: acc1, extra: xxx },
*	            	acc2: { id: 2, name: acc2, extra: yyy },
*	            	acc4: { id: 4, name: acc4, extra: zzz }
*            }
*/
function updateList(selector, data, valueField, labelField, newValue, isStrict, isForce){

  if(!selector || !data || typeof data == 'string') return;

  var valueField = valueField || 'id';
  var labelField = labelField || 'name';
  var isStrict = typeof isStrict == 'undefined' || isStrict;
  var isForce = typeof isForce !== 'undefined' && isForce;
  
  //trace(new_value);
  var target = isForce && jQuery(selector) || jQuery(selector).not('[readonly]');
  target.each(function(){
    
    var new_value = newValue || jQuery(this).val() || '';
    
    if(typeof new_value == "string")
      new_value = [new_value];
    
		if(isStrict){
    	jQuery(this).empty();
    }else{
    	jQuery(this).find('option').each(function(){
      	if(!inArray(this.value, new_value) && !inArray(this.innerHTML, new_value))
          jQuery(this).remove();
      })
    }
		
    for(i in data){

      var opt = document.createElement('option');

      opt.value = data[i][valueField] || '';
      opt.innerHTML = data[i][labelField] || '';
      opt.selected = inArray(opt.value, new_value) || inArray(opt.innerHTML, new_value);

      this.add(opt);
    }
    
    jQuery(this).trigger('change').trigger('chosen:updated');
    
  })
}

/**
* Update dropdown list
*
* sourceElementSelector = input/DOM element which hold the value
*												ex. '.acc-input' which hold the value 'acc2'
*
* sourceData 						= the object with the key will match with sourceElementSelector's value
*                       ex. {  
*	            								acc1: { id: 1, name: acc1, extra: xxx },
*	            								acc2: { id: 2, name: acc2, extra: yyy },
*	            								acc4: { id: 4, name: acc4, extra: zzz }
*                           }
*
* mapping 							= the object with the key is the target selector and the value is the key of element of each item in sourceData
*                       ex. {  
*	            								'.acc-id'		: 'id',
*	            								'.acc-code'	: 'extra',
*                           }
*
* parent								= parent selector of the sourceElementSelector, will set to 'body' if omit
*												ex. '.acc-wrap'
*/
function updateValueAfterValueChange(sourceElementSelector, sourceData, mapping, parent){
  
  var elm = jQuery(sourceElementSelector);
  if(elm.length !== 1 || !sourceData || typeof sourceData !== 'object' || isArray(sourceData) || !mapping || typeof mapping !== 'object' || isArray(mapping))
    return false; 
	
  if(!sourceData[elm.val()])
    return false;
  
  var parent = parent && elm.closest(parent) || jQuery('body');

  var data = sourceData[elm.val()];  
  
  for(selector in mapping){
    
  	var target = parent.find(selector);
    
    target.each(function(){
      
      if(this.tagName == 'SELECT' || this.tagName == 'INPUT' || this.tagName == 'TEXTAREA'){
      	this.value = data[mapping[selector]];
        jQuery(this).trigger('change');
      }
      
    });
  }  
}

/**
 * Update value with key value style
 * data = [
 						[
            	{key: 'input-acc-html-id1', value: 'acc1'},
              {key: 'input-ani-html-id1', value: 'ani1'},
              {key: 'input-address-html-id1', value: '<b>puchong</b>', type: 'html'}
              {key: 'input-address-html-id1', value: ['class', 'text-muted'], type: 'attr'}
            ],
            [
            	{key: 'input-acc-html-id2', value: 'acc2'},
              {key: 'input-ani-html-id2', value: 'ani2'}
            ],
          ]
 */

function updateValue(data){
  //trace(data);
  if(!data) return false;
  
  for(x in data){
    //trace(data[x])
    for(y in data[x]){
      if(typeof data[x][y].key != 'undefined' && typeof data[x][y].value != 'undefined' && jQuery('#'+data[x][y].key).length){
        if(data[x][y].type){
          switch(data[x][y].type){
            case 'html':
              jQuery('#'+data[x][y].key).html(data[x][y].value);
              break;
            case 'attr':
              jQuery('#'+data[x][y].key).attr(data[x][y].value[0], data[x][y].value[1]);
              break;
            case 'removeattr':
              jQuery('#'+data[x][y].key).removeAttr(data[x][y].value);
              break;
            case 'addclass':
              jQuery('#'+data[x][y].key).addClass(data[x][y].value);
              break;
            case 'removeclass':
              jQuery('#'+data[x][y].key).removeClass(data[x][y].value);
              break;
           	case 'list':
              updateList('#'+data[x][y].key, data[x][y]['list'], 'id', 'name', data[x][y].value, true, true);
              break;
            case 'data':
              jQuery('#'+data[x][y].key).data(data[x][y].value[0], data[x][y].value[1]);
              break;
          }
        }else{
        	jQuery('#'+data[x][y].key).val(data[x][y].value);
        }
      }
    }
  }
  
  updateChosen();
}

function resetForm($form){
	$form[0].reset();
  $form.find('.chosen-select').val('');
  updateChosen();
  $form.trigger('reset');
}

/*******************************
 * LOADING
 *******************************/  
function showLoading(title){
  var title = title || 'Saving';
  jQuery('#loading').find('.loading-title').html(title);
  jQuery('#loading').modal({
    backdrop: 'static',
    keyboard: true
  })
};

function hideLoading(){
  jQuery('#loading').modal('hide')
};

/*******************************
 * BTN DEL WARNING
 *******************************/  
(function($){
	$(function(){
    $('body').on('click', '.delete-warning', function(){
      return confirm('Are you sure you wanna delete this?');
    })
  })
})(jQuery);

/*******************************
 * Auto expended
 *******************************/  
(function($){
	$(function(){
    typeof autosize === 'function' && autosize($('.auto-expanded'));
  })
})(jQuery);


/*******************************
 * Disable tabindex
 *******************************/  
(function($){
	$(function(){
    $('[disabled], [readonly]').attr('tabindex', -1)
  })
})(jQuery);