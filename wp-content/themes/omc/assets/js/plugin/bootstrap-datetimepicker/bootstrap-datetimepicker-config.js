(function($){
	$.fn.datetimepicker.defaults = {
		autoclose: 1,
		todayBtn:  1,
		todayHighlight: 1,
		startView: 2,
		showMeridian: 1,
		pickerPosition: 'bottom-left',
		format: 'dd MM yyyy - hh:ii',
		
		bootcssVer: 3,
		fontAwesome: true,
		zIndex: 100
	};
})(jQuery);

/*
 * Ammend in core
 
this.component = this.element.is('.datetime-wrap') ? this.element.find('.datetime-toggle') : false,
this.componentReset = this.element.is('.datetime-wrap') ? this.element.find('.datetime-reset') : false,



if( elementOrParentIsFixed(this.element) ){
	top = top + document.body.scrollTop;
}



this.linkField = options.linkField || this.element.data('link-field') || ;
if(this.linkField && !this.linkField instanceof $)
	this.linkField = $(this.linkField);
	
if (this.linkField) {
	this.linkField.val(this.getFormattedDate(this.linkFormat));
}
*/