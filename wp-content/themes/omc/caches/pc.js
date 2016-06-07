/*! jquery.cookie v1.4.1 | MIT */
(function($){if(typeof $.cookie==='undefined'){!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?a(require("jquery")):a(jQuery)}(function(a){function b(a){return h.raw?a:encodeURIComponent(a)}function c(a){return h.raw?a:decodeURIComponent(a)}function d(a){return b(h.json?JSON.stringify(a):String(a))}function e(a){0===a.indexOf('"')&&(a=a.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));try{return a=decodeURIComponent(a.replace(g," ")),h.json?JSON.parse(a):a}catch(b){}}function f(b,c){var d=h.raw?b:e(b);return a.isFunction(c)?c(d):d}var g=/\+/g,h=a.cookie=function(e,g,i){if(void 0!==g&&!a.isFunction(g)){if(i=a.extend({},h.defaults,i),"number"==typeof i.expires){var j=i.expires,k=i.expires=new Date;k.setTime(+k+864e5*j)}return document.cookie=[b(e),"=",d(g),i.expires?"; expires="+i.expires.toUTCString():"",i.path?"; path="+i.path:"",i.domain?"; domain="+i.domain:"",i.secure?"; secure":""].join("")}for(var l=e?void 0:{},m=document.cookie?document.cookie.split("; "):[],n=0,o=m.length;o>n;n++){var p=m[n].split("="),q=c(p.shift()),r=p.join("=");if(e&&e===q){l=f(r,g);break}e||void 0===(r=f(r))||(l[q]=r)}return l};h.defaults={},a.removeCookie=function(b,c){return void 0===a.cookie(b)?!1:(a.cookie(b,"",a.extend({},c,{expires:-1})),!a.cookie(b))}});}})(jQuery);(function($){$.fn.serializeObject=function(){"use strict";var a={},b=function(b,c){var d=a[c.name];"undefined"!=typeof d&&d!==null?$.isArray(d)?d.push(c.value):a[c.name]=[d,c.value]:a[c.name]=c.value};return $.each(this.serializeArray(),b),a};})(jQuery);jQuery.fn.scrollTo=function(speed,fn){jQuery('html, body').animate({scrollTop:jQuery(this).offset().top+'px'},speed||'fast',fn);return this;}
jQuery.fn.pop=function(){var top=this.get(-1);this.splice(this.length-1,1);return top;};jQuery.fn.shift=function(){var bottom=this.get(0);this.splice(0,1);return bottom;};jQuery.fn.changeElementType=function(newType){var attrs={};if(this&&this[0]&&this[0].attributes){jQuery.each(this[0].attributes,function(idx,attr){attrs[attr.nodeName]=attr.nodeValue;});}
this.replaceWith(function(){return jQuery("<"+newType+"/>",attrs).append(jQuery(this).contents());});};jQuery.fn.state=function(state){if(typeof state!=='string')
return this;jQuery.each(this,function(idx){var self=jQuery(this);if(self.is('.btn-state')){var clone=self.find('.state.'+state).clone();self.find('.placeholder').html(clone.contents()).end().find('.state').filter('.current').removeClass('current').end().filter('.'+state).addClass('current');self.data('state',state);}});return this;};Number.prototype.pad=function(size){var s=String(this);while(s.length<(size||2)){s="0"+s;}
return s;}
String.prototype.padLeft=function(length,character){return new Array(length-this.length+1).join(character||' ')+this;};function trace(data){console.log(data);}
function arrayCompare(a1,a2){if(a1.length!=a2.length)return false;var length=a2.length;for(var i=0;i<length;i++){if(a1[i]!==a2[i])return false;}
return true;}
function inArray(needle,haystack){var length=haystack.length;for(var i=0;i<length;i++){if(typeof haystack[i]=='object'){if(arrayCompare(haystack[i],needle))return true;}else{if(haystack[i]==needle)return true;}}
return false;}
function isArray(value){return Object.prototype.toString.call(value)==='[object Array]';}
function randomString(length,chars){var length=length||16;var chars=chars||'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';var result='';for(var i=length;i>0;--i)result+=chars[Math.round(Math.random()*(chars.length-1))];return result;}
function updateQueryString(key,value,url){if(!url)url=window.location.href;var re=new RegExp("([?&])"+key+"=.*?(\&|#|\$)(.*)","gi"),hash;if(re.test(url)){if(typeof value!=='undefined'&&value!==null)
return url.replace(re,'$1'+key+"="+value+'$2$3');else{hash=url.split('#');url=hash[0].replace(re,'$1$3').replace(/(\&|\?)$/,'');if(typeof hash[1]!=='undefined'&&hash[1]!==null)
url+='#'+hash[1];return url;}}
else{if(typeof value!=='undefined'&&value!==null){var separator=url.indexOf('?')!==-1?'&':'?';hash=url.split('#');url=hash[0]+separator+key+'='+value;if(typeof hash[1]!=='undefined'&&hash[1]!==null)
url+='#'+hash[1];return url;}
else
return url;}}
function getSelectValues(select){var result=[];var options=select&&select.options;var opt;for(var i=0,iLen=options.length;i<iLen;i++){opt=options[i];if(opt.selected){result.push(opt.value||opt.text);}}
return result;}
var resolution=(function($,global){var s={},win=$(global),data={isPC:1170,isNetBook:1024,isTablet:768,isPhone:0};s.detect=function(){var width=win.width();for(key in data){if(width>=data[key])
return key;}
return'';}
return s;})(jQuery,window);function isPhone(){return resolution.detect()=='isPhone';}
function isTablet(){return resolution.detect()=='isTablet';}
function isNetBook(){return resolution.detect()=='isNetBook';}
function isPC(){return resolution.detect()=='isPC';}
function delay(){return(function(){var timer=0;return function(callback,ms){clearTimeout(timer);timer=setTimeout(callback,ms);};})();}
(function($,global){})(jQuery,window);(function(global,doc,$){$(function(){$('body').on('click.scrollTo','[data-scroll-to]',function(e){var selector=$(this).data('scroll-to')||'',target=$(selector).first();if(target.length){e.preventDefault();target.scrollTo(500);}});(function(global,$){var $btn=$('#back-to-top'),topStart=$btn.length&&$btn.data('top-start')&&parseInt($btn.data('top-start'))||0;$(global).on('scroll',function(e){$btn.toggleClass('active',(topStart<$(this).scrollTop()));})})(global,$);});$(function(){$('body').on('click.post','.js-post-like',function(e){e.preventDefault();var self=$(this),post=self.closest('.js-post'),pid=post.data('id'),likeCount=post.find('.js-post-like-count'),likeCountVal=parseInt(likeCount.html())||0;if(!pid)
return false;var data={'action':'omc_post_toggle_like','pid':pid}
$.post(info.ajaxurl,data,function(response){});if(self.toggleClass('active').is('.active'))
likeCount.html(++likeCountVal);else{likeCount.html(--likeCountVal);}});})})(window,document,jQuery);