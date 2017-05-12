function cleanEmptyValues(str){
	var strtext=''
	var res = str.split("&");
	for (i = 0; i < res.length; i++) { 
		var resSub = res[i].split("=");
		if (!isEmpty(resSub[1])){
    		strtext += res[i]+'&';
		}
	}
	return strtext.substring(0, strtext.length - 1);;
}

function isEmpty(str) {
    return (!str || 0 === str.length);
}

$(document).ready(function() { 
	$("#cvv,#cc_cvv").prop("type", "password");
	$('.system_message').hide();	
	if($('.system_message').find('.div_error').length>0 || $('.system_message').find('.div_msg').length>0){
		$('.system_message').show();
	}	
});

$(document).on('click', '.closeMsg', function(){
	   //alert($('.div_error').html());	
	   //setcookie('subsription_expire', 1, time() + (86400 * 30), "/");
	   if ( $( ".div_error" ).length ) {
		   if ($('.div_error').html().indexOf('packages') >= 0) {
			   date = new Date();
			   date.setTime(date.getTime()+(1*24*60*60*1000));
			   expires = "; expires="+date.toGMTString();
			   document.cookie = "subsription_expire=1"+expires+"; path=/";
		   }
	   }
       $('.system_message').slideUp('slow', function(){ $('.system_message').find('.div_error').remove(); $('.system_message').find('.div_msg').remove(); $('.system_message').hide();});
    })

function equalHeight(group) {
   	tallest = 0;
	group.each(function() {
		thisHeight = $(this).height();
		if(thisHeight > tallest) {
	    	tallest = thisHeight;
   	 	}
   	});
	//alert(tallest);
	group.height(tallest);
}

function ShowJsSystemMessage(msg,error,format){
	if (typeof(error)==='undefined') error = false;
	if (typeof(format)==='undefined') format = false;
	var divCls='div_msg';
	if (error)
		divCls = 'div_error';
	
	if (format)
		msg = '<div class="'+divCls+'"><ul><li>'+msg+'</li></ul></div>';
	$('.system_message').html('<a class="closeMsg" href="javascript:void(0);"></a>'+msg).show();
}
/*function ShowJsSystemMessage(msg,error=false,format=false){
	var divCls='div_msg';
	if (error)
		divCls = 'div_error';
	
	if (format)
		msg = '<div class="'+divCls+'"><ul><li>'+msg+'</li></ul></div>';
	$('.system_message').html('<a class="closeMsg" href="javascript:void(0);"></a>'+msg).show();
}*/

function HideJsSystemMessage(){
	$('.system_message').hide();
}

	
function removeHash() {
    var scrollV, scrollH, loc = window.location;
    if ('replaceState' in history) {
        history.replaceState('', document.title, loc.pathname + loc.search);
    } else {
        // Prevent scrolling by storing the page's current scroll offset
        scrollV = document.body.scrollTop;
        scrollH = document.body.scrollLeft;

        loc.hash = '';

        // Restore the scroll offset, should be flicker free
        document.body.scrollTop = scrollV;
        document.body.scrollLeft = scrollH;
    }
}

function reloadCaptcha(){
    $src = $('img.captcha').attr('src');
    $('img.captcha').attr('src', $src + '?' + Math.random());
}

function showCssElementLoading(el, append){
	if(append == 1)
		el.append('<div id="loader" class="loader">Loading...</div>');
	else
		el.html('<div id="loader" class="loader">Loading...</div>');
}

var setAutoHeightIframe = function(el) {
    $(el).bind('load', function(){
        h = $(this)[0].contentWindow.document.body.scrollHeight;
        $(this).css('min-height', h+'px');
        $(this).contents().find('form').bind('submit', function(){
            h = $(this).closest('body')[0].scrollHeight;
            $(el, window.parent.document).css('min-height', h+'px');
        });
    })

}



function generateUrl(model, action, others, use_root_url){
	if (!use_root_url) use_root_url = userwebroot;
	if (url_rewriting_enabled == 1){
		var url = use_root_url + model + '/' + action;
		if (others){
			for (x in others) others[x] = encodeURIComponent(others[x]);
			url += '/' + others.join('/');
		}
		return url;
	}
	else {
		var url = use_root_url + 'index.php?url=' + model + '/' + action;
		if (others){
			for (x in others) others[x] = encodeURIComponent(others[x]);
			url += '/' + others.join('/');
		}
		return url;
	}
}

function showHtmlElementLoading(el){
	el.html('<div class="center-display"><img src="' + webroot + 'images/ajax-loader-6.gif"></div>');
}

function showHtmlElementLoadingSmall(el){
	el.html('<div class="center-display"><img src="' + webroot + 'images/loader.jpg"></div>');
	
}

function popupwindow(url, title, w, h) {
  var left = (screen.width/2)-(w/2);
  var top = (screen.height/2)-(h/2);
  return window.open(url, '', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width='+w+',height='+h+',top='+top+',left='+left);
}

function getURLVar(key) {
	var value = [];
	var query = String(document.location).split('?');
	if (query[1]) {
		var part = query[1].split('&');
		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}
		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

function getUrlParam(){
	var urlParam="";
	var url=String(document.location);
	var param = String(document.location).split(document.domain+webroot);
	if (param[1]) {
		var attributes = String(param[1]).split('?');
		urlParam=attributes[0];
	}
	return urlParam
}



function redirectUserLoggedin() {
    if($.redirect_url!=undefined && $.redirect_url.length>0) {
        window.location.href = $.redirect_url;
        return;
    }
    if(self==top) {
        // non-iframe
        window.location.href = generateUrl('user', 'redirect');
    } else if(parent==top) {
        // iframe
        window.parent.location.href = generateUrl('user', 'redirect');
    }
}



	
$(function(){
	$(document).on('click', 'a[rel=ajax_page]', function(){
        loadList($(this).attr('href'),$(this).parent().parent().attr("lang"));
        return false;
    }) 
})

var loadList = function(href,display_div) {
    el = $('#'+display_div);
    showCssElementLoading(el);
	$('#'+display_div).load(href);
}



$(function(){
	
	//data-dismiss="alert"
	$(document).on('click', '[data-dismiss="alert"]', function(){
       $(this).parent().hide();
    })
	
    $('a[rel=search]').click(function(){
        $('.listing-search').toggle();
    })    
    //$('a[rel=page]').live('click', function(){
	$(document).on('click', 'a[rel=page]', function(){
        loadList($(this).attr('href'));
        return false;
    })
    $('form[name="frmSearch"]').submit(function(){
        loadList($.data_href);
        return false;
    })

	
	$('.date-pick').datetimepicker({timepicker:false,format:'Y-m-d',formatDate:'Y-m-d',closeOnDateSelect:true,scrollMonth:false,scrollInput: false});
    
    //$('#btn_cancel').live('click', function(){
	$(document).on('click', 'btn_cancel', function(){			
        $('.listing-search').hide();
        $('#form-div').html('');
    });
    
    $.data_href = $('#listing-div').attr('data-href');
    if($.data_href!=undefined && $.data_href!='') {
    loadList($.data_href);
    }
	
	

    onload_custom();
})

var loadList = function(href) {
    el = $('#listing-div');
    $.page_href = href;
    showHtmlElementLoading(el);

    frm = $('form[name="frmSearch"]');
    var data = frm.serialize();
    data += '&outmode=json';
    
    callAjax(href, data, function(t){
        el.html(t);
    });
}


var ajaxloadList = function(href,display_div) {
    el = $('#'+display_div);
	$('#'+display_div).load(href);
}

var reloadList = function() {
    loadList($.page_href);
}


var onload_custom = function() {
    //$("textarea").TextAreaExpander(100);
}

function trim(str)
{
    if(!str || typeof str != 'string')
        return null;
    return str.replace(/^[\s]+/,'').replace(/[\s]+$/,'').replace(/[\s]{2,}/,' ');

}


(function($) {
  $.fn.serialize_without_blank = function () {
    var $form = this,
      result,
      $disabled = $([]);

    $form.find(':input').each(function () {
      var $this = $(this);
      if ($.trim($this.val()) === '' && !$this.is(':disabled')) {
        $disabled.add($this);
        $this.attr('disabled', true);
      }
    });

    result = $form.serialize();
	//alert(result);

    $disabled.removeAttr('disabled');

    return result;
  };
}(jQuery));


	
	function applyRemoveCssClass(){
			$.each($('.listselection'), function(index, value) { 
				var elem=$(this).find('li');
				$(this).parent().parent().parent().parent().find('.listButton').removeClass('activebtn');
				if(elem.hasClass("listselect")){
					$(this).parent().parent().parent().parent().find('.listButton').addClass('activebtn');
				}
			});
	}
	
$(function(){
		applyRemoveCssClass();
		
		$(document).on('click', '.toggleLink', function(){			
    	    $(this).parent().siblings(".toggleWrap").slideToggle("slow");
	    });
		
		$(document).on('click', '.favShop,.buttonfavshop', function(event){
				event.preventDefault();
				var me=$(this);
				if ( me.data('requestRunning') ) {
     			   return;
			    }
				HideJsSystemMessage();
				me.data('requestRunning', true);
				var str_shop_id = me.attr("id");
				var arr = str_shop_id.split("_");
				var shop_id=arr[1];
				var attrItem=arr[0];
				var data = "id="+shop_id;
				data += '&outmode=json&is_ajax_request=yes';
				var href=generateUrl('common', 'favourite_shop',[shop_id]);
				callAjax(href, data, function(response){
					me.data('requestRunning', false);
					var ans = parseJsonData(response);
					if (ans.logged_in){
						if ((ans.action_performed=="R") && (me.hasClass('buttonfavshop'))){
							$("#"+attrItem+"_"+shop_id).parent().parent().hide();
						}
						me.attr("title",ans.title);
						ShowJsSystemMessage(ans.display_message,'',true);
						me.toggleClass("active");
					}else{
						login_popupbox();
					}
				})
	});
	
	$(document).on('click', '.itemfav', function(event){
			var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			HideJsSystemMessage();
			me.data('requestRunning', true);
			var str_prod_id = me.attr("id");
			var arr = str_prod_id.split("_");
			var prod_id=arr[1];
			var attrItem=arr[0];
			var data = "id="+prod_id;
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('common', 'favourite_product',[prod_id]);
			callAjax(href, data, function(response){
				me.data('requestRunning', false);
				var ans = parseJsonData(response);
				if (ans.logged_in){
					if(ans === false){
						return false;
					}
					if ((ans.action_performed=="R") || (ans.action_performed=="A")){
						ShowJsSystemMessage(ans.display_message,'',true);
						me.toggleClass("active");
						me.attr("title",ans.title);
					}
				}else{
					login_popupbox();
				}
			})
	});
	
		
	$(document).on('mouseleave', '.sectionList', function(event){
			$(this).find('.listButton').removeClass("active");
            $(this).find('.listcontainer').hide();
			return false;
    });
	
	$('.ratings').raty({
			score: function() {
			return $(this).attr('data-score');
		},
		readOnly: true,
		space : false,
		width : 100,
	});
	
	$(document).on('click', '.listtem', function(event){	
		var me = $(this);
		event.preventDefault();
		if ( me.data('requestRunning') ) {
     	   return;
		}
		HideJsSystemMessage();
		var list_id=me.attr("id");
		var prod_id=me.attr("rel");
		el = $('#display-div'+prod_id)
		showHtmlElementLoadingSmall(el);
		me.data('requestRunning', true);
		var data = "list_id="+list_id+"&prod="+prod_id;
		data += '&outmode=json&is_ajax_request=yes';
		var href=generateUrl('common', 'add_remove_list_item',[]);
		callAjax(href, data, function(response){
			me.data('requestRunning', false);
			var ans = parseJsonData(response);
			if (ans.logged_in){
				ShowJsSystemMessage(ans.display_message);
				var href=$("#product_"+prod_id).attr("href");		
				$.ajax({url: href,async: false}).done(function( html ) {
					el.html(html);
					applyRemoveCssClass(); 
				});
			}else{
				login_popupbox();
			}
		})
	})
	
	$(document).on('blur', '.check_username', function(event){		
			var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			el = $('#ajax_availability_username');
			el.removeClass('availables').removeClass('not-availables');
		    showHtmlElementLoadingSmall(el);
	
			me.data('requestRunning', true);
			var strVal = me.val();
			var data = "username="+encodeURIComponent(strVal);
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('user', 'check_username_availability',[],webroot);
         	callAjax(href, data, function(response){
				me.data('requestRunning', false);
				var ans = parseJsonData(response);
				if (ans.check==1){
					el.removeClass('not-availables').addClass('availables');
				}else if (ans.check==2){
					el.removeClass('availables').addClass('not-availables');
				}else{
					el.removeClass('availables').removeClass('not-availables');
				}
				el.html(ans.message);
			})
	})
	
	$(document).on('blur', '.check_email', function(event){		
			var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			el = $('#ajax_availability_email');
			el.removeClass('availables').removeClass('not-availables');
		    showHtmlElementLoadingSmall(el);
	
			me.data('requestRunning', true);
			var strVal = me.val();
			var data = "email="+encodeURIComponent(strVal);
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('user', 'check_email_availability',[],webroot);
         	callAjax(href, data, function(response){
				me.data('requestRunning', false);
				var ans = parseJsonData(response);
				if (ans.check==1){
					el.removeClass('not-availables').addClass('availables');
				}else if (ans.check==2){
					el.removeClass('availables').addClass('not-availables');
				}else{
					el.removeClass('availables').removeClass('not-availables');
				}
				el.html(ans.message);
			})
	})
	
	
	
	$(document).on('click', '.listView', function(event){		
		event.preventDefault();
		var me = $(this);
		if ( me.data('requestRunning') ) {
     	   return;
		}
		me.data('requestRunning', true);
		var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(logged) {
							if (logged==true){
								me.toggleClass("active");
				                me.siblings('.listcontainer').slideToggle("600");
								var href=me.attr("href");
								var str_prod_id = me.attr("id");
								var arr = str_prod_id.split("_");
								var prod_id=arr[1];
								var id =".display-div"+prod_id;
								$.ajax({url: href,async: false}).done(function( html ) {
									me.siblings(id).html(html);
									applyRemoveCssClass(); 
								});
							}else{
								login_popupbox();
							}
							me.data('requestRunning', false);
					});
		})
		
		$(document).on('click', '.ppc_promotion_click', function(event){
			event.preventDefault();		
			var me = $(this);
			if ( me.data('requestRunning') ) {
     	   		return;
			}
			me.data('requestRunning', true);
			var href=generateUrl('common', 'promotion_track_clicks',[],webroot);
			promotion_id = $(this).closest( ".thumb_click" ).attr("data-attr-id");
         	callAjax(href, 'u='+promotion_id, function(response){
				$( location ).attr("href", me.attr('href'));
			})
		})
		
		

		
	
	
	
	function findValue(li) {
		if( li == null ) 
		if( !!li.extra ) var sValue = li.extra[0];
		else var sValue = li.selectValue;
	}
	function selectItem(li) {
			findValue(li);
			
	}
	function formatItem(row) {
		return row[0];
	}
	
	$(document).on('click', '.filter_brands,.price,.states,.filter_condition', function(event){			
				event.preventDefault();
				var me=$(this);
				id= me.attr('id');
				var cls=me.attr('rel');
				$('.'+cls+':checkbox[value="' + id + '"]').attr('checked', false);
				filterRecords();
	});	
	
	//$(".filter").live("click",function(event) {
	$(document).on('click', '.filter', function(event){
		event.preventDefault();
	});	
	
	
});	
	
	
	function add_ajax_list(frm,v){
		var me=$(this);
		if ( me.data('requestRunning') ) {
			return;
		}
		me.data('requestRunning', true);
		HideJsSystemMessage();
				var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(logged) {
							if (logged==true){
								v.validate();
								if(!v.isValid()) return;
								var data = getFrmData(frm);
								data += '&outmode=json&is_ajax_request=yes';
								var id=get_query_var(data,'product_id');
								var random_id=get_query_var(data,'random_id');
								el = $('#frmList'+random_id).parent().parent();
								showHtmlElementLoadingSmall(el);
								var href=generateUrl('common', 'create_list_item',[]);
								callAjax(href, data, function(response){
								me.data('requestRunning', false);
								var ans = parseJsonData(response);
								ShowJsSystemMessage(ans.display_message);
								
								var href=$("#product_"+id).attr("href");		
								$.ajax({url: href,async: false}).done(function( html ) {
										el.html(html);
										applyRemoveCssClass(); 
									});
								})
							}else{  login_popupbox(); }
							me.data('requestRunning', false); 
					});
	}
	
	function get_query_var (querystring, name){
		var regex= name + "=([^&]+)"
		if(!new RegExp(regex).test(querystring)){
 		   return false;
		}
  		var filter = new RegExp(regex);
 	 	return unescape(querystring.match(filter)[1]);
	}
	
	
	function filterRecords(retVal){
		var href=$("#listing-div").attr("data-href");
		var brands=[];
		$("input:checkbox[name=brands]:checked").each(function(){
			brands.push($(this).val());
		});
		if (brands.length)
			href=href+"&brand="+[brands];
		
		var price_range=[];
		$("input:checkbox[name=price_range]:checked").each(function(){
			price_range.push($(this).val());
		});
		
		if (price_range.length)
			href=href+"&price_range="+[price_range];
		
		
		var shop_loc=[];
		$("input:checkbox[name=shop_location]:checked").each(function(){
			shop_loc.push($(this).val());
		});
		if (shop_loc.length)
			href=href+"&shop_loc="+[shop_loc];
			
		var ships_to=[];
		$("input:checkbox[name=ships_to]:checked").each(function(){
			ships_to.push($(this).val());
		});
		if (ships_to.length)
			href=href+"&ships_to="+[ships_to];	
			
		
		$("input:checkbox[name=out_of_stock]:checked").each(function(){
			href=href+"&out_of_stock=1";
		});
		
		var condition=[];
		$("input:checkbox[name=condition]:checked").each(function(){
			condition.push($(this).val());
		});
		if ((condition.length==1) || (condition.length==2))
			href=href+"&condition="+[condition];
			
		$("input:checkbox[name=free_shipping]:checked").each(function(){
			href=href+"&property=prod_ship_free";
		});
		
		var val = $("#sortby").val();
		//alert(val)
		if (typeof(val) != "undefined"){
			var splitted = val.split("_");
			href = href+"&sort_by="+splitted[0]+"&sort_order="+splitted[1];
		}
		//alert(href);
		var category=$(".brand_categories").filter(".selected_link").attr("id")
	    if (typeof category != 'undefined')
			href=href+"&category="+category;
		
		$(".jscroll-added").remove();
		if(typeof(retVal)=='undefined') 
			refreshTable(href);
		return href;	
}
	
function refreshTable(href){
			var pane = $('#listing-div');
			showCssElementLoading(pane)
			pane.load(href, function() {
        	pane.data('jscroll', null);
	        pane.jscroll({
					autoTrigger: false,
		          });
 			  });
    }
	
	
function login_popupbox() {
	/*Custombox.open({
          target: generateUrl('user', 'login_popup'),
		  zIndex: '10000',
		  overlayOpacity: '0.5'
    });*/
	
	jQuery.magnificPopup.open({
			items: [
	    		{
			        src: generateUrl('user', 'login_popup'),
					preloader: false,
        			type: 'ajax'
		    	}
   			 ]
		});
    //$.facebox(function() {
	  // 	$.ajax({url: generateUrl('user', 'login_popup'),async: false}).done(function(html) { $.facebox(html); });
    //});
   
}

function toggleDisabled(el,enable){
	try {
		if (enable==false){
				//el.style.color='';
			}else{
				//el.style.color='#ccc';
			}
		el.disabled =  enable ;
	}	catch(E){
		}
	try {
			if (el.childNodes && el.childNodes.length > 0) 
			{
				for (var x = 0; x < el.childNodes.length; x++) {
				toggleDisabled(el.childNodes[x],enable);
			}
		}
	}
	catch(E){
	}
}


$.fn.clearForm = function() {
  return this.each(function() {
    var type = this.type, tag = this.tagName.toLowerCase();
    if (tag == 'form')
      return $(':input',this).clearForm();
    if (type == 'text' || type == 'password' || tag == 'textarea')
      this.value = '';
    else if (type == 'checkbox' || type == 'radio')
      this.checked = false;
    else if (tag == 'select')
      this.selectedIndex = -1;
  });
};

	

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();
	
			$.extend(this, option);
	
			$(this).attr('autocomplete', 'off');
			
			// Focus
			$(this).on('focus', function() {
				this.request();
			});
			
			
			
			// Blur
			$(this).on('blur', function(event) {setTimeout(function(object) {
					object.hide();
					object.clear();
				}, 200, this);	
			});
			
			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
					case 9: // tab	
						this.hide();
						this.clear();
						break;
					/*case 38:
                    	this.moveUp();
                    break;*/
                	case 40:
                    	this.moveDown();
                    break;	
					default:
						times = 0;
						this.request();
						break;
				}				
			});
			
			// Click
			this.click = function(event) {
				event.preventDefault();
	
				value = $(event.target).parent().attr('data-value');
				
				if (value && this.items[value]) {
					//alert(this.items[value]);
					this.select(this.items[value]);
				}
			}
			
			// Show
			this.show = function() {
				var pos = $(this).position();
	
				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});
	
				$(this).siblings('ul.dropdown-menu').show();
			}
			
			// Clear
			this.clear = function() {
					$('ul.dropdown-menu').hide();
					$.map([200,400,600,1000,1500,2000], function(time,index){ 
						 setTimeout(function(){
							 $(this).siblings('ul.dropdown-menu').hide();
							},time);
					}); 
					
				
				//$(this).siblings('ul.dropdown-menu').hide();
			}	
			
			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}
			
			// moveUp
			/*this.moveUp = function() { 
            	var that = this;
	            if (that.selectedIndex === -1) {
    	            return;
        	    }
	            if (that.selectedIndex === 0) {
    	            $(that.suggestionsContainer).children().first().removeClass(that.classes.selected);
        	        that.selectedIndex = -1;
            	    that.el.val(that.currentValue);
                	that.findBestHint();
	                return;
    	        }
	            that.adjustScroll(that.selectedIndex - 1);
			}*/
			
			
			
			// moveDown
			this.moveDown = function() {
				  var that = this;
	    	       if (that.selectedIndex === (that.suggestions.length - 1)) {
    	            return;
		           }
		           that.adjustScroll(that.selectedIndex + 1);
			}		
			
			
			// Request
			this.request = function() {
				clearTimeout(this.timer);
		
				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}
			
			// Response
			this.response = function(json) {
				
				html = '';
	
				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}
	
					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}
	
					// Get all the ones with a categories
					var category = new Array();
	
					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}
	
							category[json[i]['category']]['item'].push(json[i]);
						}
					}
	
					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';
	
						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li  data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}
	
				if (html) {
					this.show();
				} else {
					this.hide();
				}
	
				$(this).siblings('ul.dropdown-menu').html(html);
			}
			
			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));	
			
		});
	}
})(window.jQuery);	


function loadStates(c_el, state_val){
	cid = parseInt(c_el.value);
	if(isNaN(cid) || cid < 1) return false;
	//alert(generateUrl('common', 'loadDropDown', [cid, 'states'], webroot));
	callAjax(generateUrl('common', 'loadDropDown', [cid, 'states'], webroot), 'a=1', function(t){
		var ans = parseJsonData(t);
		if(ans === false){
			return false
		}
		var target_el = $('#ua_state');
		if(target_el.length < 1) target_el = $(c_el).parents('form:first').find('select[name="ua_state"]');
		target_el.html('');
		//target_el.append('<option value="">State</option>');
		$.each(ans, function(i, value){
			target_el.append('<option value="'+i+'">'+value+'</option>');
		});
		if(typeof state_val != 'undefined' && state_val != null && state_val != 0){
			target_el.val(state_val);
		}
		return false;
	});
	return false;
}

function popupImage(input){
	var file = input.files[0];
	var max_file_size = 1048576;
	var sizeinbytes = file.size;
	if (sizeinbytes > max_file_size){
		//ShowJsSystemMessage(js_error_file_size.replace('{file_name}',file.name).replace('{file_size}',formatSizeUnits(max_file_size)),true,true);
		//return;
	}
	
	var _validFileExtensions = [".jpg", ".jpeg", ".gif", ".png"];
	var sFileName = file.name;
	if (sFileName.length > 0) {
		var blnValid = false;
		for (var j = 0; j < _validFileExtensions.length; j++) {
			var sCurExtension = _validFileExtensions[j];
			
			if (sFileName.substr(sFileName.length - sCurExtension.length, sCurExtension.length).toLowerCase() == sCurExtension.toLowerCase()) {
				blnValid = true;
				break;
			}
		}
		
		if (!blnValid) {
			ShowJsSystemMessage(js_error_file_extensions.replace('{file_name}',file.name).replace('{allowed_extensions}',_validFileExtensions.join(", ")),true,true);
			return false;
		}
	}
	
	//alert(input.files[0].size);
	$.magnificPopup.open({
			items: {
      			src: '<div id="loader" class="loader">Loading...</div>'
           	},
	});
	wid = $(window).width();
	if(wid >767){
		wid = 500; 
	}else{
		wid = 280;
	}
	var defaultform = "#frmProfile";
	$("#avatar-action").val("demo_avatar");
	$(defaultform).ajaxSubmit({ 
		delegation: true,
		success: function(json){
			json = $.parseJSON(json);
			if(json.status == "1"){
				$("#avatar-action").val("avatar");
				var fn = "sumbmitProfileImage();";
				
				$.magnificPopup.open({
					items: {
              			src: '<div class="img-container "><img alt="Picture" src="" class="img_responsive" id="new-img" /></div><div class="aligncenter popup_buttons"><a href="#" class="btn blue small icn-svg" title="'+$("#rotate_left").val()+'" data-option="-90" data-method="rotate">'+$("#rotate_left").val()+'</a>&nbsp;<a onclick='+fn+' href="#" class="btn green small icn-svg">'+$("#update_profile_img").val()+'</a>&nbsp;<a href="#" class="btn blue small icn-svg" title="'+$("#rotate_right").val()+'" data-option="90" data-method="rotate" type="button">'+$("#rotate_right").val()+'</a></div>'
		            	},
				});
		
				
				$('#new-img').attr('src', json.msg);
				$('#new-img').width(wid);
				cropImage($('#new-img'));
				
			}else{
				$.magnificPopup.open({
					items: {
              			src: '<div class="img-container marginTop20"><div class="div_error"><ul><li>'+json.msg+'</div></div>'
		            	},
				});
			}
		}
	});
}
var $image ;
function cropImage(obj){
	$image = obj;
	$image.cropper({
	 aspectRatio: 1,
	  autoCropArea: 0.4545,
	 // strict: true,
	  guides: false,
	  highlight: false,
	  dragCrop: false,
	  cropBoxMovable: false,
	  cropBoxResizable: false,
	  rotatable:true,
	  responsive: true,
	   crop: function (e) {
			var json = [
			'{"x":' + e.x,
			'"y":' + e.y,
			'"height":' + e.height,
			'"width":' + e.width,
			'"rotate":' + e.rotate + '}'
			].join();
		$("#img-data").val(json);
	  },
	   built: function () {
		$(this).cropper("zoom", 0.5);
	  }, 
	 
		})
}


$(document).ready(function() {
		
	
	
	$('#search_keyword').devbridgeAutocomplete({
			 minChars:2,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'search_producttags_autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: false,
	    	 onSelect: function (suggestion) {
				 $("#frmSiteSearch").submit();
        	 //alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
    	 }
	});
	
});



function Slugify(str,str_val_id,is_slugify){
	var str = str.toString().toLowerCase()
    .replace(/\s+/g, '-')           // Replace spaces with -
    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
    .replace(/^-+/, '')             // Trim - from start of text
    .replace(/-+$/, '');   
	if ($("#"+is_slugify).val()==0)
		$("#"+str_val_id).val(str);
}

function copyValue(str,str_val_id,is_edit){
	if ($("#"+is_edit).val()==0)
		$("#"+str_val_id).val(str);
}
	

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
}

$(function () { 

	$(document.body).on('click', '[data-method]', function () {
		var data = $(this).data(),
          $target,
          result;
	
      if (data.method) {
        data = $.extend({}, data); // Clone a new one
        if (typeof data.target !== 'undefined') {
          $target = $(data.target);
          if (typeof data.option === 'undefined') {
            try {
              data.option = JSON.parse($target.val());
            } catch (e) {
              console.log(e.message);
            }
          }
        }
        result = $image.cropper(data.method, data.option);
		if (data.method === 'getCroppedCanvas') {
          $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);
        }

        if ($.isPlainObject(result) && $target) {
          try {
            $target.val(JSON.stringify(result));
          } catch (e) {
            console.log(e.message);
          }
        }
		
      }
    });
		

});

$(function () {
    var elem = "";
    var settings = {
        mode: "toggle",
        limit: 500,
    };
    var text = "";
    $.fn.viewMore = function (options) {

        $.extend(settings, options)
        text = $(this).html();
        elem = this;
        initialize();
    };

    function initialize() {

        $(elem).each(function () {

            var extraText = $(this).html().substr(settings.limit, $(this).html().length)
            if ($(this).html().length > settings.limit + 50) {
                $(this).html($(this).html().substr(0, settings.limit));
                $(this).append("<span style='display:none' class='read_more'>" + extraText + "</span>")
                $(this).append("<span class='read_more_toggle link'>" + ".. Read More" + "</span>");
            }
        });
    }
	$(document).on('click','.read_more_toggle',function () {
        $(this).parent().find('.read_more').toggle();

        if ($(this).parent().find('.read_more').is(':visible')) {
            $(this).text('.. Read Less');
        } else {
            $(this).text('.. Read More');
        }
    });
});

$(document).ready(function () {
	$(".accordion h4:first").addClass("active");
    $(".accordion h4:first").addClass("active");
    $(".accordion .ans:not(:first)").hide();
    $(" .accordion  h4").click(function () {
        $(this).next(".ans").slideToggle("slow", "linear")
                .siblings(".ans:visible").slideUp("slow", "linear");
        $(this).toggleClass("active");
        $(this).siblings("h4").removeClass("active");
    });

    // $(".accordion .accordion_icon:first").addClass("active");
    $(".accordion .sub_cat:not(:first)").hide();
    $(".accordion .accordion_icon").click(function () {
        $(this).next(".sub_cat").slideToggle("slow", "linear")
                .siblings(".sub_cat:visible").slideUp("slow", "linear");
        $(this).toggleClass("active");
        $(this).siblings(".accordion_icon").removeClass("active");
    });
	
	$(".accordion .accordion-heading:first").addClass("active");
    $(".accordion .ans:not(:first)").hide();
    $(".accordion .accordion-heading").click(function () {
        $(this).next(".ans").slideToggle("slow", "linear")
                .siblings(".ans:visible").slideUp("slow", "linear");
        $(this).toggleClass("active");
        $(this).siblings(".accordion-heading").removeClass("active");
    });
	
	$('.round_sectn .sectnTop').click(function () {
        $(this).toggleClass("active");
        if ($(window).width() < 1001) {
            $(this).siblings('.round_sectn .sectnMiddle').slideToggle("slow", "linear");
        }
    });	
});

function validDate(s) {
	var bits = s.split('-');
  	var d = new Date(bits[0] + '/' + bits[1] + '/' + bits[2]);
  	return !!(d && (d.getMonth() + 1) == bits[1] && d.getDate() == Number(bits[2]));
 }

function AddDaysToDate(sDate, iAddDays, sSeperator) {
	if (!validDate(sDate)){
		return;
	}
	var date = new Date(sDate);
	if (iAddDays=="D"){
		iAddDays = 1
	}else if (iAddDays=="W"){
		iAddDays = 7
	}else if (iAddDays=="M"){
		iAddDays = daysInMonth(date.getMonth()+1,date.getFullYear())
	}
    //Purpose: Add the specified number of dates to a given date.
    
    date.setDate(date.getDate() + parseInt(iAddDays));
    //var sEndDate = LPad(date.getMonth() + 1, 2) + sSeperator + LPad(date.getDate(), 2) + sSeperator + date.getFullYear();
	var sEndDate = date.getFullYear() + sSeperator + LPad(date.getMonth() + 1, 2) + sSeperator + LPad(date.getDate(), 2) ;
    return sEndDate;
}

function daysInMonth(month, year) {
    return new Date(year, month, 0).getDate();
}

function LPad(sValue, iPadBy) {
    sValue = sValue.toString();
    return sValue.length < iPadBy ? LPad("0" + sValue, iPadBy) : sValue;
}

//$("#ppc_loader").bind("content_loaded", ppc_track_impressions);
$(document).ready(function () {
	ppc_track_impressions();
})
function ppc_track_impressions() {
		$(".ppc-campaign .thumb_impression").each(function() {
				var me = $(this);
				var href=generateUrl('common', 'promotion_track_impressions',[],webroot);
				$.ajax({
            	async: true,
            	type: "POST",
            	url: href,
            	data: 'u='+$(this).attr("data-attr-id"),
            	success: function (response) {
               	me.removeClass('thumb_impression');
            	}
        	});
       	
		})
}

$(document).ready(function() { 
	$('a[rel=fancy_popup_box]').on('click', function(event){
		event.preventDefault();
		var target_href= $(this).attr("href");
		var href=generateUrl('common', 'check_ajax_user_logged_in');
		$.ajax({url: href,async: false}).done(function(logged) {
		if (logged==true){
				$.magnificPopup.open({
				  items: {
      					src: target_href
				  },		
				  type: 'ajax',
				  alignTop: true,
				  overflowY: 'scroll' // as we know that popup content is tall we set scroll overflow by default to avoid jump
				});
		}else{  login_popupbox(); }
		});
	});
});


			/**/

function open_demo_popup(){
	jQuery.magnificPopup.open({
				items: [
	    			{
			        	src: generateUrl('custom', 'request_demo',[],webroot),
						preloader: false,
        				type: 'ajax'
		    		}
   			 	]
			});
}
		
$(document).ready(function() {
		$('body').on('click', '.request-demo',function() {		
			//alert('A');
			open_demo_popup();
		})
});



/* for sticky left panel */ 
if($(window).width()>1050){ 
		function sticky_relocate() { 
				var window_top = $(window).scrollTop(); 
				var div_top = $('.fixed__panel').offset().top -110; 
				var sticky_left = $('#fixed__panel'); 
				if((window_top + sticky_left.height()) >= ($('.footer').offset().top - 40)){
					var to_reduce = ((window_top + sticky_left.height()) - ($('.footer').offset().top - 40)); 
					var set_stick_top = -40 - to_reduce; 
					sticky_left.css('top', set_stick_top+'px'); 
				}else{
					sticky_left.css('top', '110px'); 
					if (window_top > div_top) {
						$('#fixed__panel').addClass('stick'); 
					} else {
						$('#fixed__panel').removeClass('stick'); 
					} 
				}
			} 
			$(function () { 
				if ( $( "#fixed__panel" ).length ){
					//$(window).scroll(sticky_relocate); 
					//sticky_relocate();
				} 
			});
		} 

/*if($(window).width()>1050){ 
	function sticky_relocate() {
			$('#fixed__panel').stickySidebar({
				headerSelector: 'header', // defines header section ('header' by default)
				navSelector: '.navpanel', // defines navigation ('nav' by default)
				contentSelector: '.right-panel', // defines content section ('#content' by default)
				footerSelector: 'footer', // defines footer section ('footer' by default)
				sidebarTopMargin: 50, // defines top margin from sidebar to navigation element (20px by default)
				footerThreshold: 100 // defines a distance from footer (40px by default)
			});
		} $(function () { 
		if ( $( "#fixed__panel" ).length ){
			$(window).scroll(sticky_relocate); 
			sticky_relocate(); 
		}
	}); 
} */



function callAjax(strURL,strPostData,thefunction) {
    if(callAjaxExecuting){
        addToCallAjaxQ(strURL, strPostData, thefunction);
        return;
    }
    callAjaxExecuting=true;
    var xmlHttpReq = false;
    var self = this;
    var msg="";
    // Mozilla/Safari
    
    var www=(window.location.href.toLowerCase().indexOf("//www.")>0)?"http://www.":"http://";
    var strURL=strURL.replace("http://",www);

    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
    self.xmlHttpReq.open('POST', strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) 
        {
            callAjaxExecuting=false;
            if(self.xmlHttpReq.status != 200 && !userLeavingPage){
				$.mbsmessage('Connection Error.....\nPlease Check your Internet Connection.');
              	 // alert('OOPS! Connection Error.\nPlease Check your Internet Connection.');
                return;
            }
            if($.trim(self.xmlHttpReq.responseText)=='{"status":0,"msg":"Your Session seems to have expired. Please try refreshing the page to login again."}'){
                alert('Your Session seems to have expired. Please try refreshing the page to login again.');
                thefunction('');
                return;
            }
            thefunction(self.xmlHttpReq.responseText);
            setTimeout('executeFromCallAjaxQ();', 5) ;
        }
        else
        {
			$.mbsmessage.close();
            //alert(self.xmlHttpReq.readyState);
        }
    }
    
    self.xmlHttpReq.send(strPostData);
}

function formatSizeUnits(bytes){
      if      (bytes>=1073741824) {bytes=(bytes/1073741824).toFixed(2)+' GB';}
      else if (bytes>=1048576)    {bytes=(bytes/1048576).toFixed(2)+' MB';}
      else if (bytes>=1024)       {bytes=(bytes/1024).toFixed(2)+' KB';}
      else if (bytes>1)           {bytes=bytes+' bytes';}
      else if (bytes==1)          {bytes=bytes+' byte';}
      else                        {bytes='0 byte';}
      return bytes;
}


function htmlEncode(value) {
	 return $('<textarea/>').text(value).html();
}

function htmlDecode(value) {
	return $("<textarea/>").html(value).text();
}

$('body').on('click', '.pop-us-tabs ul li a',function() {  
			
		if ($(this).hasClass("dnc")) { 
			return;
		}
		
		//$(".tabs_content").hide();
		var activeTab = $(this).attr("rel");
		if (activeTab=="tabs_1"){
			$('#demo_btn_submit').prop("value",'Next');
		}else{
			$('#demo_btn_submit').prop("value",'Confirm Demo request');
		}
		$(".tabs_content").removeClass('show_tab_content');
		$("#"+activeTab).addClass('show_tab_content');
		//$("#"+activeTab).fadeIn();		
		$(".pop-us-tabs ul li").removeClass("active");
		$(this).parent().addClass("active");
    });
$('.pop-us-tabs ul li a').last().addClass("tab_last");
function loadDatePicker(obj){
	obj.datetimepicker({
		timepicker: false,
		format:'Y-m-d',
		formatDate:'Y-m-d',
		minDate: new Date(),
		onSelectDate: function(event) {
			obj.parent().find("ul").remove();
		}
    });
}

function loadTimePicker(obj){
	obj.datetimepicker({
		datepicker: false,
		format:'H:i',		
		allowTimes:[
		  '09:00', '09:30', '10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00', '17:30', '18:00', '18:30', '19:00', '19:30'
		 ],
		step:'30',
		onSelectTime: function(event) {
			obj.parent().find("ul").remove();
		}
    });
}

$('body').on('focus', '#preferred_demo_date_first',function() { 
	loadDatePicker($(this));
});

$('body').on('focus', '#preferred_demo_date_second',function() { 
	loadDatePicker($(this));
});

$('body').on('focus', '#preferred_demo_date_third',function() { 
	loadDatePicker($(this));
});

$('body').on('focus', '#preferred_demo_time_first',function() { 
	loadTimePicker($(this));
});

$('body').on('focus', '#preferred_demo_time_second',function() {
	loadTimePicker($(this));
});

$('body').on('focus', '#preferred_demo_time_third',function() {	
	loadTimePicker($(this));
});

var chk=false;
function validateRequestForm(frm,v){
	HideJsSystemMessage();
	
	v.validate();
	if(!v.isValid()) {
		var activeTab = '';
		$('a[rel="tabs_2"]').addClass('dnc');
		$('#frmRequestDemo').each(function(){
		var validationFailedTab = $(this).find('.error').closest('.tabs_content').attr('id');	
		if(validationFailedTab.length > 0 ){activeTab = validationFailedTab;}
		});	
		$(".tabs_content").removeClass('show_tab_content');
		$("#"+activeTab).addClass('show_tab_content');
		$(".pop-us-tabs ul li").removeClass("active");
		$('a[rel="'+activeTab+'"]').parent().addClass('active');	
		$('a[rel="'+activeTab+'"]').removeClass('dnc');
		if (activeTab=="tabs_2"){
			$('#demo_btn_submit').prop("value",'Confirm Demo request');
			if (chk==false){
				setTimeout(function(){ $('#tabs_2').find("ul").empty(); }, 500);
			}
			chk=true;
			loadDatePicker($('#preferred_demo_date_first'));
			loadDatePicker($('#preferred_demo_date_second'));
			loadDatePicker($('#preferred_demo_date_third'));
			loadTimePicker($('#preferred_demo_time_first'));
			loadTimePicker($('#preferred_demo_time_second'));
			loadTimePicker($('#preferred_demo_time_third'));
		}
					
		return false;
	}
	var me=$(this);
	me.data('requestRunning', true);
	var data = getFrmData(frm);
	data += '&outmode=json&is_ajax_request=yes';
	var href=generateUrl('custom', 'request_demo',[],webroot);
	callAjax(href, data, function(response){
		me.data('requestRunning', false);
		var json = parseJsonData(response);
		ShowJsSystemMessage(json['msg']);
		if (json['status']){
			$("#frmRequestDemo").clearForm();
			$.magnificPopup.close();
		}
	})
	return false;
	
}
