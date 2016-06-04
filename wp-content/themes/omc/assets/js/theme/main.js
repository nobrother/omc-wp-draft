/**
*
*	General functions, variable, settings
* On current project
*
* @ Project :	mvsc
* @ Description	: 
*
*
* Created by Chang
* Company		:	omc
* Modified	: 15 Oct 2014
*
*/

/*! jquery.cookie v1.4.1 | MIT */
(function($){
	if(typeof $.cookie === 'undefined'){		
		!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?a(require("jquery")):a(jQuery)}(function(a){function b(a){return h.raw?a:encodeURIComponent(a)}function c(a){return h.raw?a:decodeURIComponent(a)}function d(a){return b(h.json?JSON.stringify(a):String(a))}function e(a){0===a.indexOf('"')&&(a=a.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));try{return a=decodeURIComponent(a.replace(g," ")),h.json?JSON.parse(a):a}catch(b){}}function f(b,c){var d=h.raw?b:e(b);return a.isFunction(c)?c(d):d}var g=/\+/g,h=a.cookie=function(e,g,i){if(void 0!==g&&!a.isFunction(g)){if(i=a.extend({},h.defaults,i),"number"==typeof i.expires){var j=i.expires,k=i.expires=new Date;k.setTime(+k+864e5*j)}return document.cookie=[b(e),"=",d(g),i.expires?"; expires="+i.expires.toUTCString():"",i.path?"; path="+i.path:"",i.domain?"; domain="+i.domain:"",i.secure?"; secure":""].join("")}for(var l=e?void 0:{},m=document.cookie?document.cookie.split("; "):[],n=0,o=m.length;o>n;n++){var p=m[n].split("="),q=c(p.shift()),r=p.join("=");if(e&&e===q){l=f(r,g);break}e||void 0===(r=f(r))||(l[q]=r)}return l};h.defaults={},a.removeCookie=function(b,c){return void 0===a.cookie(b)?!1:(a.cookie(b,"",a.extend({},c,{expires:-1})),!a.cookie(b))}});
	}	
})(jQuery);

/*
 * jQuery: scrollTo
 */
jQuery.fn.scrollTo = function(speed, fn) {
  jQuery('html, body').animate({
    scrollTop: jQuery(this).offset().top + 'px'
  }, speed || 'fast', fn);
  return this; // for chaining...
}

/*
 * jQuery: Pop the last item in a jQuery object
 */
jQuery.fn.pop = function() {
  var top = this.get(-1);
  this.splice(this.length-1,1);
  return top;
};

/*
 * jQuery: Shift the last item in a jQuery object
 */
jQuery.fn.shift = function() {
  var bottom = this.get(0);
  this.splice(0,1);
  return bottom;
};

/*
 * jQuery: Change HTML tag
 */
jQuery.fn.changeElementType = function(newType) {
  var attrs = {};
	
  if( this && this[0] && this[0].attributes ){
    jQuery.each(this[0].attributes, function(idx, attr) {
      attrs[attr.nodeName] = attr.nodeValue;
    });
  }

  this.replaceWith(function() {
    return jQuery("<" + newType + "/>", attrs).append(jQuery(this).contents());
  });
};

/*
 * jQuery: Change btn state
 */
jQuery.fn.state = function(state) {
	
	if(typeof state !== 'string')
		return this;
	
	jQuery.each(this, function(idx){
		var self = jQuery(this);
		if(self.is('.btn-state')){
			
			// Clone to placeholder
			var clone = self.find('.state.'+state).clone();
			
			self
				.find('.placeholder').html(clone.contents())
				.end().find('.state')
				.filter('.current').removeClass('current')
				.end()
				.filter('.'+state).addClass('current');
			self.data('state', state);
		}
	});
	
	return this;
};

/*
 * Prototype: Pad number
 */
Number.prototype.pad = function(size) {
  var s = String(this);
  while (s.length < (size || 2)) {s = "0" + s;}
  return s;
}

/*
 * Prototype: Pad string
 */
String.prototype.padLeft = function (length, character) { 
    return new Array(length - this.length + 1).join(character || ' ') + this; 
};

/*
 * Debug
 */
function trace(data){
	console.log(data);
}


/*
 * Array: compare two array object
 */
function arrayCompare(a1, a2) {
  if (a1.length != a2.length) return false;
  var length = a2.length;
  for (var i = 0; i < length; i++) {
    if (a1[i] !== a2[i]) return false;
  }
  return true;
}

/*
 * Array: Check a item is in an array
 */
function inArray(needle, haystack) {
  var length = haystack.length;
  for(var i = 0; i < length; i++) {
    if(typeof haystack[i] == 'object') {
      if(arrayCompare(haystack[i], needle)) return true;
    } else {
      if(haystack[i] == needle) return true;
    }
  }
  return false;
}

/*
 * Array: Check the variable is an array
 */
function isArray(value){
  return Object.prototype.toString.call(value) === '[object Array]';
}

/*
 * Function: Generate random string
 */
function randomString(length, chars) {
  var length = length || 16;
  var chars = chars || '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var result = '';
  for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
  return result;
}

/*
 * Function: Modify URL query
 */
function updateQueryString(key, value, url) {
  if (!url) url = window.location.href;
  
  var re = new RegExp("([?&])" + key + "=.*?(\&|#|\$)(.*)", "gi"),
      hash;

  if (re.test(url)) {
    if (typeof value !== 'undefined' && value !== null)
      return url.replace(re, '$1' + key + "=" + value + '$2$3');
    else {
      hash = url.split('#');
      url = hash[0].replace(re, '$1$3').replace(/(\&|\?)$/, '');
      if (typeof hash[1] !== 'undefined' && hash[1] !== null) 
        url += '#' + hash[1];
      return url;
    }
  }
  else {
    if (typeof value !== 'undefined' && value !== null) {
      var separator = url.indexOf('?') !== -1 ? '&' : '?';
      hash = url.split('#');
      url = hash[0] + separator + key + '=' + value;
      if (typeof hash[1] !== 'undefined' && hash[1] !== null) 
        url += '#' + hash[1];
      return url;
    }
    else
      return url;
  }
}

/*
 * Function: Get selected value from <select>
 */
function getSelectValues(select) {
  var result = [];
  var options = select && select.options;
  var opt;

  for (var i=0, iLen=options.length; i<iLen; i++) {
    opt = options[i];

    if (opt.selected) {
      result.push(opt.value || opt.text);
    }
  }
  return result;
}

/*
 * Function: Detect resolution
 */
var resolution = (function($, global){
	var s = {},
			win = $(global),
			data = { 
				isPC : 1170,
				isNetBook : 1024,
				isTablet : 768,
				isPhone : 0
			};
	s.detect = function(){
		var width = win.width();
		for(key in data){
			if(width >= data[key])
				return key;
		}
		return '';
	}
	
	return s;
})(jQuery, window);
function isPhone(){
	return resolution.detect() == 'isPhone';
}
function isTablet(){
	return resolution.detect() == 'isTablet';
}
function isNetBook(){
	return resolution.detect() == 'isNetBook';
}
function isPC(){
	return resolution.detect() == 'isPC';
}


/*
 * Routine
 */
(function($,global){
	
})(jQuery, window);