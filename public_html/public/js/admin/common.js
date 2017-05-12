/* Start respond.min.js */
/*
 * respond.js - A small and fast polyfill for min/max-width CSS3 Media Queries
 * Copyright 2011, Scott Jehl, scottjehl.com
 * Dual licensed under the MIT or GPL Version 2 licenses. 
 * Usage: Check out the readme file or github.com/scottjehl/respond
*/
(function(e,h){e.respond={};respond.update=function(){};respond.mediaQueriesSupported=h;if(h){return}var u=e.document,r=u.documentElement,i=[],k=[],p=[],o={},g=30,f=u.getElementsByTagName("head")[0]||r,b=f.getElementsByTagName("link"),d=[],a=function(){var B=b,w=B.length;for(var z=0;z<w;z++){var y=B[z],x=y.href,A=y.media,v=y.rel&&y.rel.toLowerCase()==="stylesheet";if(!!x&&v&&!o[x]){if(!/^([a-zA-Z]+?:(\/\/)?(www\.)?)/.test(x)||x.replace(RegExp.$1,"").split("/")[0]===e.location.host){d.push({href:x,media:A})}else{o[x]=true}}}t()},t=function(){if(d.length){var v=d.shift();n(v.href,function(w){m(w,v.href,v.media);o[v.href]=true;t()})}},m=function(G,v,x){var E=G.match(/@media ([^\{]+)\{((?!@media)[\s\S])*(?=\}[\s]*\/\*\/mediaquery\*\/)/gmi),H=E&&E.length||0,v=v.substring(0,v.lastIndexOf("/")),w=function(I){return I.replace(/(url\()['"]?([^\/\)'"][^:\)'"]+)['"]?(\))/g,"$1"+v+"$2$3")},y=!H&&x;if(v.length){v+="/"}if(y){H=1}for(var B=0;B<H;B++){var C;if(y){C=x;k.push(w(G))}else{C=E[B].match(/@media ([^\{]+)\{([\S\s]+?)$/)&&RegExp.$1;k.push(RegExp.$2&&w(RegExp.$2))}var z=C.split(","),F=z.length;for(var A=0;A<F;A++){var D=z[A];i.push({media:D.match(/(only\s+)?([a-zA-Z]+)(\sand)?/)&&RegExp.$2,rules:k.length-1,minw:D.match(/\(min\-width:[\s]*([\s]*[0-9]+)px[\s]*\)/)&&parseFloat(RegExp.$1),maxw:D.match(/\(max\-width:[\s]*([\s]*[0-9]+)px[\s]*\)/)&&parseFloat(RegExp.$1)})}}j()},l,q,j=function(E){var v="clientWidth",x=r[v],D=u.compatMode==="CSS1Compat"&&x||u.body[v]||x,z={},C=u.createDocumentFragment(),B=b[b.length-1],w=(new Date()).getTime();if(E&&l&&w-l<g){clearTimeout(q);q=setTimeout(j,g);return}else{l=w}for(var y in i){var F=i[y];if(!F.minw&&!F.maxw||(!F.minw||F.minw&&D>=F.minw)&&(!F.maxw||F.maxw&&D<=F.maxw)){if(!z[F.media]){z[F.media]=[]}z[F.media].push(k[F.rules])}}for(var y in p){if(p[y]&&p[y].parentNode===f){f.removeChild(p[y])}}for(var y in z){var G=u.createElement("style"),A=z[y].join("\n");G.type="text/css";G.media=y;if(G.styleSheet){G.styleSheet.cssText=A}else{G.appendChild(u.createTextNode(A))}C.appendChild(G);p.push(G)}f.insertBefore(C,B.nextSibling)},n=function(v,x){var w=c();if(!w){return}w.open("GET",v,true);w.onreadystatechange=function(){if(w.readyState!=4||w.status!=200&&w.status!=304){return}x(w.responseText)};if(w.readyState==4){return}w.send()},c=(function(){var v=false,w=[function(){return new ActiveXObject("Microsoft.XMLHTTP")},function(){return new ActiveXObject("Msxml3.XMLHTTP")},function(){return new ActiveXObject("Msxml2.XMLHTTP")},function(){return new XMLHttpRequest()}],y=w.length;while(y--){try{v=w[y]()}catch(x){continue}break}return function(){return v}})();a();respond.update=a;function s(){j(true)}if(e.addEventListener){e.addEventListener("resize",s,false)}else{if(e.attachEvent){e.attachEvent("onresize",s)}}})(this,(function(f){if(f.matchMedia){return true}var e,i=document,c=i.documentElement,g=c.firstElementChild||c.firstChild,h=!i.body,d=i.body||i.createElement("body"),b=i.createElement("div"),a="only all";b.id="mq-test-1";b.style.cssText="position:absolute;top:-99em";d.appendChild(b);b.innerHTML='_<style media="'+a+'"> #mq-test-1 { width: 9px; }</style>';if(h){c.insertBefore(d,g)}b.removeChild(b.firstChild);e=b.offsetWidth==9;if(h){c.removeChild(d)}else{d.removeChild(b)}return e})(this));
/* End respond.min.js */



/* Start pirobox_extended.js */

/**
* Name: PiroBox Extended v.1.0
* Date: Gen 2011
* Autor: Diego Valobra (http://www.pirolab.it),(http://www.diegovalobra.com)
* Version: 1.0
* Licence: CC-BY-SA http://creativecommons.org/licenses/by-sa/3/it/
**/

(function($) {
	$.fn.piroBox_ext = function(opt) {
		opt = jQuery.extend({
		piro_speed : 700,
		bg_alpha : 0.9,
		piro_scroll : true
		}, opt);
	$.fn.piroFadeIn = function(speed, callback) {
		$(this).fadeIn(speed, function() {
		if(jQuery.browser.msie)
			$(this).get(0).style.removeAttribute('filter');
		if(callback != undefined)
			callback();
		});
	};
	$.fn.piroFadeOut = function(speed, callback) {
		$(this).fadeOut(speed, function() {
		if(jQuery.browser.msie)
			$(this).get(0).style.removeAttribute('filter');
		if(callback != undefined)
			callback();
		});
	};
	var my_gall_obj = $('a[class*="pirobox"]');
	var map = new Object();
		for (var i=0; i<my_gall_obj.length; i++) {
			var it=$(my_gall_obj[i]);
			map['a.'+ it.attr('class').match(/^pirobox_gall\w*/)]=0;
		}
	var gall_settings = new Array();
	for (var key in map) {
		gall_settings.push(key);
	}
	for (var i=0; i<gall_settings.length; i++) {
		$(gall_settings[i]+':first').addClass('first');
		$(gall_settings[i]+':last').addClass('last');
	}
	var piro_gallery = $(my_gall_obj);
	$('a[class*="pirobox_gall"]').each(function(rev){this.rev = rev+0});
	var struct =(
		'<div class="piro_overlay"></div>'+
		'<table class="piro_html"  cellpadding="0" cellspacing="0">'+
		'<tr>'+
		'<td class="h_t_l"></td>'+
		'<td class="h_t_c" title="drag me!!"></td>'+
		'<td class="h_t_r"></td>'+
		'</tr>'+
		'<tr>'+
		'<td class="h_c_l"></td>'+
		'<td class="h_c_c">'+
		'<div class="piro_loader" title="close"><span></span></div>'+
		'<div class="resize">'+
		'<div class="nav_container">'+
		'<a href="#prev" class="piro_prev" title="previous"></a>'+
		'<a href="#next" class="piro_next" title="next"></a>'+
		'<div class="piro_prev_fake">prev</div>'+
		'<div class="piro_next_fake">next</div>'+
		'<div class="piro_close" title="close"></div>'+
		'</div>'+
		'<div class="caption"></div>'+
		'<div class="div_reg"></div>'+
		'</div>'+
		'</td>'+
		'<td class="h_c_r"></td>'+
		'</tr>'+
		'<tr>'+
		'<td class="h_b_l"></td>'+
		'<td class="h_b_c"></td>'+
		'<td class="h_b_r"></td>'+
		'</tr>'+
		'</table>'
		);
	$('body').append(struct);
	var wrapper = $('.piro_html'),
	piro_capt = $('.caption'),
	piro_bg = $('.piro_overlay'),
	piro_next = $('.piro_next'),
	piro_prev = $('.piro_prev'),
	piro_next_fake = $('.piro_next_fake'),
	piro_prev_fake = $('.piro_prev_fake'),
	piro_close = $('.piro_close'),
	div_reg = $('.div_reg'),
	piro_loader = $('.piro_loader'),
	resize = $('.resize'),
	btn_info = $('.btn_info');
	var rz_img =0.95; /*::::: ADAPT IMAGE TO BROWSER WINDOW SIZE :::::*/
	if ( $.browser.msie ) {
		wrapper.draggable({ handle:'.h_t_c,.h_b_c,.div_reg img'});
	}else{
		wrapper.draggable({ handle:'.h_t_c,.h_b_c,.div_reg img',opacity: 0.80});
	}	
	var y = $(window).height();
	var x = $(window).width();
	$('.nav_container').hide();
	wrapper.css({left:  ((x/2)-(250))+ 'px',top: parseInt($(document).scrollTop())+(100)});
	$(wrapper).add(piro_capt).add(piro_bg).hide();
	piro_bg.css({'opacity':opt.bg_alpha});	
	$(piro_prev).add(piro_next).bind('click',function(c) {
		$('.nav_container').hide();
		c.preventDefault();
		piro_next.add(piro_prev).hide();
		var obj_count = parseInt($('a[class*="pirobox_gall"]').filter('.item').attr('rev'));
		var start = $(this).is('.piro_prev') ? $('a[class*="pirobox_gall"]').eq(obj_count - 1) : $('a[class*="pirobox_gall"]').eq(obj_count + 1);
		start.click();
	});
	$('html').bind('keyup', function (c) {
		 if(c.keyCode == 27) {
			c.preventDefault();
			if($(piro_close).is(':visible')){close_all();}
		}
	});
	$('html').bind('keyup' ,function(e) {
		 if ($('.item').is('.first')){
		}else if(e.keyCode == 37){
		e.preventDefault();
			if($(piro_close).is(':visible')){piro_prev.click();}
		 }
	});
	$('html').bind('keyup' ,function(z) {
		if ($('.item').is('.last')){
		}else if(z.keyCode == 39){
		z.preventDefault();
			if($(piro_close).is(':visible')){piro_next.click();}
		}
	});
	$(window).resize(function(){
		var new_y = $(window).height();
		var new_x = $(window).width();
		var new_h = wrapper.height();
		var new_w = wrapper.width();
		wrapper.css({
			left:  ((new_x/2)-(new_w/2))+ 'px',
			top: parseInt($(document).scrollTop())+(new_y-new_h)/2
			});			  
	});	
	function scrollIt (){
		$(window).scroll(function(){
			var new_y = $(window).height();
			var new_x = $(window).width();
			var new_h = wrapper.height();
			var new_w = wrapper.width();
			wrapper.css({
				left:  ((new_x/2)-(new_w/2))+ 'px',
				top: parseInt($(document).scrollTop())+(new_y-new_h)/2
			});			  
		});
	}
	if(opt.piro_scroll == true){
		scrollIt()
	}
	$(piro_gallery).each(function(){

		var descr = $(this).attr('title');
		var params = $(this).attr('rel').split('-');
		var p_link = $(this).attr('href');
		$(this).unbind(); 
		$(this).bind('click', function(e) {
			piro_bg.css({'opacity':opt.bg_alpha});	
			e.preventDefault();
			piro_next.add(piro_prev).hide().css('visibility','hidden');
			$(piro_gallery).filter('.item').removeClass('item');
			$(this).addClass('item');
			open_all();
			if($(this).is('.first')){
				piro_prev.hide();
				piro_next.show();
				piro_prev_fake.show().css({'opacity':0.5,'visibility':'hidden'});
			}else{
				piro_next.add(piro_prev).show();
				piro_next_fake.add(piro_prev_fake).hide();	  
			}
			if($(this).is('.last')){
				piro_prev.show();
				piro_next_fake.show().css({'opacity':0.5,'visibility':'hidden'});
				piro_next.hide();	
			}
			if($(this).is('.pirobox')){
				piro_next.add(piro_prev).hide();	
			}

		});

	function open_all(){
			wrapper.add(piro_bg).add(div_reg).add(piro_loader).show();
			function animate_html(){
				if(params[1] == 'full' && params[2] == 'full'){
				params[2] = $(window).height()-70;	
				params[1] = $(window).width()-55;
				}
				var y = $(window).height();
				var x = $(window).width();
				piro_close.hide();
				div_reg.add(resize).animate({
					'height':+ (params[2]) +'px',
					'width':+ (params [1])+'px'
					},opt.piro_speed).css('visibility','visible');
					
				wrapper.animate({
					height:+ (params[2])+20 +'px',
					width:+ (params[1]) +20+'px',
					left:  ((x/2)-((params[1])/2+10))+ 'px',
					top: parseInt($(document).scrollTop())+(y-params[2])/2-10
					},opt.piro_speed ,function(){
						piro_next.add(piro_prev).css({'height':'20px','width':'20px'});
						piro_next.add(piro_prev).add(piro_prev_fake).add(piro_next_fake).css('visibility','visible');
						$('.nav_container').show();
						piro_close.show();
				});
			}
			function animate_image (){
						var img = new Image();
						img.onerror = function (){
							piro_capt.html('');
							img.src = "http://www.pirolab.it/pirobox/js/error.jpg";
						}
						img.onload = function() {
							piro_capt.add(btn_info).hide();	
							var y = $(window).height();
							var x = $(window).width();
							var	imgH = img.height;
							var	imgW = img.width;
							
							//var rz_img =1.203; /*::::: ORIGINAL SIZE :::::*/
							if(imgH+20 > y || imgW+20 > x){
								var _x = (imgW + 20)/x;
								var _y = (imgH + 20)/y;
								if ( _y > _x ){
									imgW = Math.round(img.width* (rz_img/_y));
									imgH = Math.round(img.height* (rz_img/_y));
								}else{
									imgW = Math.round(img.width * (rz_img/_x));
									imgH = Math.round(img.height * (rz_img/_x));
								}
							}else{
								 imgH = img.height;
								 imgW = img.width;
								}

							var y = $(window).height();
							var x = $(window).width();
							$(img).height(imgH).width(imgW).hide();
							
							$(img).fadeOut(300,function(){});
								$('.div_reg img').remove();
								$('.div_reg').html('');
								div_reg.append(img).show();
							$(img).addClass('immagine');
							
							div_reg.add(resize).animate({height:imgH+'px',width:imgW+'px'},opt.piro_speed);
							wrapper.animate({
								height : (imgH+20) + 'px' ,
								width : (imgW+20) + 'px' , 
								left:  ((x/2)-((imgW+20)/2)) + 'px',
								top: parseInt($(document).scrollTop())+(y-imgH)/2-20
								},opt.piro_speed, function(){
									var cap_w = resize.width();
									piro_capt.css({width:cap_w+'px'});
									piro_loader.hide();				
									$(img).fadeIn(300,function(){
									piro_close.add(btn_info).show();
									piro_capt.slideDown(200);								
									piro_next.add(piro_prev).css({'height':'20px','width':'20px'});
									piro_next.add(piro_prev).add(piro_prev_fake).add(piro_next_fake).css('visibility','visible');
									$('.nav_container').show();
									resize.resize(function(){
										NimgW = img.width;//1.50;
										NimgH = img.heigh;//1.50;
										piro_capt.css({width:(NimgW)+'px'});
									});	
								});	
							});	
						}
						
						img.src = p_link;
						piro_loader.click(function(){
						img.src = 'about:blank';
					});
				}

			switch (params[0]) {
				
				case 'iframe':
					div_reg.html('').css('overflow','hidden');
					resize.css('overflow','hidden');
					piro_close.add(btn_info).add(piro_capt).hide();
					animate_html();
					div_reg.piroFadeIn(300,function(){
						div_reg.append(
						'<iframe id="my_frame" class="my_frame" src="'+p_link+'" frameborder="0" allowtransparency="true" scrolling="auto" align="top"></iframe>'
						);
						$('.my_frame').css({'height':+ (params[2]) +'px','width':+ (params [1])+'px'});
						piro_loader.hide();
					});
				break;
				
				case 'content':
					div_reg.html('').css('overflow','auto');
					resize.css('overflow','auto');
					$('.my_frame').remove();
					piro_close.add(btn_info).add(piro_capt).hide();
					animate_html()	
					div_reg.piroFadeIn(300,function(){
						div_reg.load(p_link);
						piro_loader.hide();
					});
				break;
				
				case 'inline':
					div_reg.html('').css('overflow','auto');
					resize.css('overflow','auto');
					$('.my_frame').remove();
					piro_close.add(btn_info).add(piro_capt).hide();
					animate_html()	
					div_reg.piroFadeIn(300,function(){
						$(p_link).clone(true).appendTo(div_reg).piroFadeIn(300);
						piro_loader.hide();
					});
				break	
							
				case 'gallery':
					div_reg.css('overflow','hidden');
					resize.css('overflow','hidden');
					$('.my_frame').remove();
					piro_close.add(btn_info).add(piro_capt).hide();
					if(descr == ""){
						piro_capt.html('');
						}else{
					piro_capt.html('<p>' + descr + '</p>');
					}
					animate_image();
				break;
				
				case 'single':
					piro_close.add(btn_info).add(piro_capt).hide();
					div_reg.html('').css('overflow','hidden');
					resize.css('overflow','hidden');
					$('.my_frame').remove();
					if(descr == ""){
						piro_capt.html('');
						}else{
					piro_capt.html('<p>' + descr + '</p>');
					}
					animate_image();
				break
			} 	
		}
	});
	
	//$('.immagine').live('click',function(){
	$(document).on('click', '.immagine', function(event){		
		piro_capt.slideToggle(200);
	});
	
	function close_all (){
		if($('.piro_close').is(':visible')){
			$('.my_frame').remove();
			wrapper.add(div_reg).add(resize).stop();
			var ie_sucks = wrapper;
			if ( $.browser.msie ) {
			ie_sucks = div_reg.add(piro_bg);
			$('.div_reg img').remove();
			}else{
			ie_sucks = wrapper.add(piro_bg);
			}
			ie_sucks.piroFadeOut(200,function(){
				div_reg.html('');
				piro_loader.add(piro_capt).add(btn_info).hide();
				$('.nav_container').hide();
				piro_bg.add(wrapper).hide().css('visibility','visible');
			});
			}
		}
		piro_close.add(piro_loader).add(piro_bg).bind('click',function(y){y.preventDefault(); close_all(); });	
	}
})(jQuery);

/* End pirobox_extended.js */

/* Start waypoints.min.js */
/*
jQuery Waypoints - v1.1.4
Copyright (c) 2011-2012 Caleb Troughton
Dual licensed under the MIT license and GPL license.
https://github.com/imakewebthings/jquery-waypoints/blob/master/MIT-license.txt
https://github.com/imakewebthings/jquery-waypoints/blob/master/GPL-license.txt
*/
(function($,k,m,i,d){var e=$(i),g="waypoint.reached",b=function(o,n){o.element.trigger(g,n);if(o.options.triggerOnce){o.element[k]("destroy")}},h=function(p,o){var n=o.waypoints.length-1;while(n>=0&&o.waypoints[n].element[0]!==p[0]){n-=1}return n},f=[],l=function(n){$.extend(this,{element:$(n),oldScroll:0,waypoints:[],didScroll:false,didResize:false,doScroll:$.proxy(function(){var q=this.element.scrollTop(),p=q>this.oldScroll,s=this,r=$.grep(this.waypoints,function(u,t){return p?(u.offset>s.oldScroll&&u.offset<=q):(u.offset<=s.oldScroll&&u.offset>q)}),o=r.length;if(!this.oldScroll||!q){$[m]("refresh")}this.oldScroll=q;if(!o){return}if(!p){r.reverse()}$.each(r,function(u,t){if(t.options.continuous||u===o-1){b(t,[p?"down":"up"])}})},this)});$(n).scroll($.proxy(function(){if(!this.didScroll){this.didScroll=true;i.setTimeout($.proxy(function(){this.doScroll();this.didScroll=false},this),$[m].settings.scrollThrottle)}},this)).resize($.proxy(function(){if(!this.didResize){this.didResize=true;i.setTimeout($.proxy(function(){$[m]("refresh");this.didResize=false},this),$[m].settings.resizeThrottle)}},this));e.load($.proxy(function(){this.doScroll()},this))},j=function(n){var o=null;$.each(f,function(p,q){if(q.element[0]===n){o=q;return false}});return o},c={init:function(o,n){this.each(function(){var u=$.fn[k].defaults.context,q,t=$(this);if(n&&n.context){u=n.context}if(!$.isWindow(u)){u=t.closest(u)[0]}q=j(u);if(!q){q=new l(u);f.push(q)}var p=h(t,q),s=p<0?$.fn[k].defaults:q.waypoints[p].options,r=$.extend({},s,n);r.offset=r.offset==="bottom-in-view"?function(){var v=$.isWindow(u)?$[m]("viewportHeight"):$(u).height();return v-$(this).outerHeight()}:r.offset;if(p<0){q.waypoints.push({element:t,offset:null,options:r})}else{q.waypoints[p].options=r}if(o){t.bind(g,o)}if(n&&n.handler){t.bind(g,n.handler)}});$[m]("refresh");return this},remove:function(){return this.each(function(o,p){var n=$(p);$.each(f,function(r,s){var q=h(n,s);if(q>=0){s.waypoints.splice(q,1)}})})},destroy:function(){return this.unbind(g)[k]("remove")}},a={refresh:function(){$.each(f,function(r,s){var q=$.isWindow(s.element[0]),n=q?0:s.element.offset().top,p=q?$[m]("viewportHeight"):s.element.height(),o=q?0:s.element.scrollTop();$.each(s.waypoints,function(u,x){if(!x){return}var t=x.options.offset,w=x.offset;if(typeof x.options.offset==="function"){t=x.options.offset.apply(x.element)}else{if(typeof x.options.offset==="string"){var v=parseFloat(x.options.offset);t=x.options.offset.indexOf("%")?Math.ceil(p*(v/100)):v}}x.offset=x.element.offset().top-n+o-t;if(x.options.onlyOnScroll){return}if(w!==null&&s.oldScroll>w&&s.oldScroll<=x.offset){b(x,["up"])}else{if(w!==null&&s.oldScroll<w&&s.oldScroll>=x.offset){b(x,["down"])}else{if(!w&&o>x.offset){b(x,["down"])}}}});s.waypoints.sort(function(u,t){return u.offset-t.offset})})},viewportHeight:function(){return(i.innerHeight?i.innerHeight:e.height())},aggregate:function(){var n=$();$.each(f,function(o,p){$.each(p.waypoints,function(q,r){n=n.add(r.element)})});return n}};$.fn[k]=function(n){if(c[n]){return c[n].apply(this,Array.prototype.slice.call(arguments,1))}else{if(typeof n==="function"||!n){return c.init.apply(this,arguments)}else{if(typeof n==="object"){return c.init.apply(this,[null,n])}else{$.error("Method "+n+" does not exist on jQuery "+k)}}}};$.fn[k].defaults={continuous:true,offset:0,triggerOnce:false,context:i};$[m]=function(n){if(a[n]){return a[n].apply(this)}else{return a.aggregate()}};$[m].settings={resizeThrottle:200,scrollThrottle:100};e.load(function(){$[m]("refresh")})})(jQuery,"waypoint","waypoints",this);
/* End waypoints.min.js */



/* Start modernizr-1.7.min.js */
// Modernizr v1.7  www.modernizr.com
window.Modernizr=function(a,b,c){function G(){e.input=function(a){for(var b=0,c=a.length;b<c;b++)t[a[b]]=!!(a[b]in l);return t}("autocomplete autofocus list placeholder max min multiple pattern required step".split(" ")),e.inputtypes=function(a){for(var d=0,e,f,h,i=a.length;d<i;d++)l.setAttribute("type",f=a[d]),e=l.type!=="text",e&&(l.value=m,l.style.cssText="position:absolute;visibility:hidden;",/^range$/.test(f)&&l.style.WebkitAppearance!==c?(g.appendChild(l),h=b.defaultView,e=h.getComputedStyle&&h.getComputedStyle(l,null).WebkitAppearance!=="textfield"&&l.offsetHeight!==0,g.removeChild(l)):/^(search|tel)$/.test(f)||(/^(url|email)$/.test(f)?e=l.checkValidity&&l.checkValidity()===!1:/^color$/.test(f)?(g.appendChild(l),g.offsetWidth,e=l.value!=m,g.removeChild(l)):e=l.value!=m)),s[a[d]]=!!e;return s}("search tel url email datetime date month week time datetime-local number range color".split(" "))}function F(a,b){var c=a.charAt(0).toUpperCase()+a.substr(1),d=(a+" "+p.join(c+" ")+c).split(" ");return!!E(d,b)}function E(a,b){for(var d in a)if(k[a[d]]!==c&&(!b||b(a[d],j)))return!0}function D(a,b){return(""+a).indexOf(b)!==-1}function C(a,b){return typeof a===b}function B(a,b){return A(o.join(a+";")+(b||""))}function A(a){k.cssText=a}var d="1.7",e={},f=!0,g=b.documentElement,h=b.head||b.getElementsByTagName("head")[0],i="modernizr",j=b.createElement(i),k=j.style,l=b.createElement("input"),m=":)",n=Object.prototype.toString,o=" -webkit- -moz- -o- -ms- -khtml- ".split(" "),p="Webkit Moz O ms Khtml".split(" "),q={svg:"http://www.w3.org/2000/svg"},r={},s={},t={},u=[],v,w=function(a){var c=b.createElement("style"),d=b.createElement("div"),e;c.textContent=a+"{#modernizr{height:3px}}",h.appendChild(c),d.id="modernizr",g.appendChild(d),e=d.offsetHeight===3,c.parentNode.removeChild(c),d.parentNode.removeChild(d);return!!e},x=function(){function d(d,e){e=e||b.createElement(a[d]||"div");var f=(d="on"+d)in e;f||(e.setAttribute||(e=b.createElement("div")),e.setAttribute&&e.removeAttribute&&(e.setAttribute(d,""),f=C(e[d],"function"),C(e[d],c)||(e[d]=c),e.removeAttribute(d))),e=null;return f}var a={select:"input",change:"input",submit:"form",reset:"form",error:"img",load:"img",abort:"img"};return d}(),y=({}).hasOwnProperty,z;C(y,c)||C(y.call,c)?z=function(a,b){return b in a&&C(a.constructor.prototype[b],c)}:z=function(a,b){return y.call(a,b)},r.flexbox=function(){function c(a,b,c,d){a.style.cssText=o.join(b+":"+c+";")+(d||"")}function a(a,b,c,d){b+=":",a.style.cssText=(b+o.join(c+";"+b)).slice(0,-b.length)+(d||"")}var d=b.createElement("div"),e=b.createElement("div");a(d,"display","box","width:42px;padding:0;"),c(e,"box-flex","1","width:10px;"),d.appendChild(e),g.appendChild(d);var f=e.offsetWidth===42;d.removeChild(e),g.removeChild(d);return f},r.canvas=function(){var a=b.createElement("canvas");return a.getContext&&a.getContext("2d")},r.canvastext=function(){return e.canvas&&C(b.createElement("canvas").getContext("2d").fillText,"function")},r.webgl=function(){return!!a.WebGLRenderingContext},r.touch=function(){return"ontouchstart"in a||w("@media ("+o.join("touch-enabled),(")+"modernizr)")},r.geolocation=function(){return!!navigator.geolocation},r.postmessage=function(){return!!a.postMessage},r.websqldatabase=function(){var b=!!a.openDatabase;return b},r.indexedDB=function(){for(var b=-1,c=p.length;++b<c;){var d=p[b].toLowerCase();if(a[d+"_indexedDB"]||a[d+"IndexedDB"])return!0}return!1},r.hashchange=function(){return x("hashchange",a)&&(b.documentMode===c||b.documentMode>7)},r.history=function(){return !!(a.history&&history.pushState)},r.draganddrop=function(){return x("dragstart")&&x("drop")},r.websockets=function(){return"WebSocket"in a},r.rgba=function(){A("background-color:rgba(150,255,150,.5)");return D(k.backgroundColor,"rgba")},r.hsla=function(){A("background-color:hsla(120,40%,100%,.5)");return D(k.backgroundColor,"rgba")||D(k.backgroundColor,"hsla")},r.multiplebgs=function(){A("background:url(//:),url(//:),red url(//:)");return(new RegExp("(url\\s*\\(.*?){3}")).test(k.background)},r.backgroundsize=function(){return F("backgroundSize")},r.borderimage=function(){return F("borderImage")},r.borderradius=function(){return F("borderRadius","",function(a){return D(a,"orderRadius")})},r.boxshadow=function(){return F("boxShadow")},r.textshadow=function(){return b.createElement("div").style.textShadow===""},r.opacity=function(){B("opacity:.55");return/^0.55$/.test(k.opacity)},r.cssanimations=function(){return F("animationName")},r.csscolumns=function(){return F("columnCount")},r.cssgradients=function(){var a="background-image:",b="gradient(linear,left top,right bottom,from(#9f9),to(white));",c="linear-gradient(left top,#9f9, white);";A((a+o.join(b+a)+o.join(c+a)).slice(0,-a.length));return D(k.backgroundImage,"gradient")},r.cssreflections=function(){return F("boxReflect")},r.csstransforms=function(){return!!E(["transformProperty","WebkitTransform","MozTransform","OTransform","msTransform"])},r.csstransforms3d=function(){var a=!!E(["perspectiveProperty","WebkitPerspective","MozPerspective","OPerspective","msPerspective"]);a&&"webkitPerspective"in g.style&&(a=w("@media ("+o.join("transform-3d),(")+"modernizr)"));return a},r.csstransitions=function(){return F("transitionProperty")},r.fontface=function(){var a,c,d=h||g,e=b.createElement("style"),f=b.implementation||{hasFeature:function(){return!1}};e.type="text/css",d.insertBefore(e,d.firstChild),a=e.sheet||e.styleSheet;var i=f.hasFeature("CSS2","")?function(b){if(!a||!b)return!1;var c=!1;try{a.insertRule(b,0),c=/src/i.test(a.cssRules[0].cssText),a.deleteRule(a.cssRules.length-1)}catch(d){}return c}:function(b){if(!a||!b)return!1;a.cssText=b;return a.cssText.length!==0&&/src/i.test(a.cssText)&&a.cssText.replace(/\r+|\n+/g,"").indexOf(b.split(" ")[0])===0};c=i('@font-face { font-family: "font"; src: url(data:,); }'),d.removeChild(e);return c},r.video=function(){var a=b.createElement("video"),c=!!a.canPlayType;if(c){c=new Boolean(c),c.ogg=a.canPlayType('video/ogg; codecs="theora"');var d='video/mp4; codecs="avc1.42E01E';c.h264=a.canPlayType(d+'"')||a.canPlayType(d+', mp4a.40.2"'),c.webm=a.canPlayType('video/webm; codecs="vp8, vorbis"')}return c},r.audio=function(){var a=b.createElement("audio"),c=!!a.canPlayType;c&&(c=new Boolean(c),c.ogg=a.canPlayType('audio/ogg; codecs="vorbis"'),c.mp3=a.canPlayType("audio/mpeg;"),c.wav=a.canPlayType('audio/wav; codecs="1"'),c.m4a=a.canPlayType("audio/x-m4a;")||a.canPlayType("audio/aac;"));return c},r.localstorage=function(){try{return!!localStorage.getItem}catch(a){return!1}},r.sessionstorage=function(){try{return!!sessionStorage.getItem}catch(a){return!1}},r.webWorkers=function(){return!!a.Worker},r.applicationcache=function(){return!!a.applicationCache},r.svg=function(){return!!b.createElementNS&&!!b.createElementNS(q.svg,"svg").createSVGRect},r.inlinesvg=function(){var a=b.createElement("div");a.innerHTML="<svg/>";return(a.firstChild&&a.firstChild.namespaceURI)==q.svg},r.smil=function(){return!!b.createElementNS&&/SVG/.test(n.call(b.createElementNS(q.svg,"animate")))},r.svgclippaths=function(){return!!b.createElementNS&&/SVG/.test(n.call(b.createElementNS(q.svg,"clipPath")))};for(var H in r)z(r,H)&&(v=H.toLowerCase(),e[v]=r[H](),u.push((e[v]?"":"no-")+v));e.input||G(),e.crosswindowmessaging=e.postmessage,e.historymanagement=e.history,e.addTest=function(a,b){a=a.toLowerCase();if(!e[a]){b=!!b(),g.className+=" "+(b?"":"no-")+a,e[a]=b;return e}},A(""),j=l=null,f&&a.attachEvent&&function(){var a=b.createElement("div");a.innerHTML="<elem></elem>";return a.childNodes.length!==1}()&&function(a,b){function p(a,b){var c=-1,d=a.length,e,f=[];while(++c<d)e=a[c],(b=e.media||b)!="screen"&&f.push(p(e.imports,b),e.cssText);return f.join("")}function o(a){var b=-1;while(++b<e)a.createElement(d[b])}var c="abbr|article|aside|audio|canvas|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",d=c.split("|"),e=d.length,f=new RegExp("(^|\\s)("+c+")","gi"),g=new RegExp("<(/*)("+c+")","gi"),h=new RegExp("(^|[^\\n]*?\\s)("+c+")([^\\n]*)({[\\n\\w\\W]*?})","gi"),i=b.createDocumentFragment(),j=b.documentElement,k=j.firstChild,l=b.createElement("body"),m=b.createElement("style"),n;o(b),o(i),k.insertBefore(m,k.firstChild),m.media="print",a.attachEvent("onbeforeprint",function(){var a=-1,c=p(b.styleSheets,"all"),k=[],o;n=n||b.body;while((o=h.exec(c))!=null)k.push((o[1]+o[2]+o[3]).replace(f,"$1.iepp_$2")+o[4]);m.styleSheet.cssText=k.join("\n");while(++a<e){var q=b.getElementsByTagName(d[a]),r=q.length,s=-1;while(++s<r)q[s].className.indexOf("iepp_")<0&&(q[s].className+=" iepp_"+d[a])}i.appendChild(n),j.appendChild(l),l.className=n.className,l.innerHTML=n.innerHTML.replace(g,"<$1font")}),a.attachEvent("onafterprint",function(){l.innerHTML="",j.removeChild(l),j.appendChild(n),m.styleSheet.cssText=""})}(a,b),e._enableHTML5=f,e._version=d,g.className=g.className.replace(/\bno-js\b/,"")+" js "+u.join(" ");return e}(this,this.document)
/* End modernizr-1.7.min.js */

