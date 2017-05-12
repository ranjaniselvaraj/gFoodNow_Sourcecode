var matched, browser;
jQuery.uaMatch = function( ua ) {
    ua = ua.toLowerCase();
    var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
        /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
        /(msie)[\s?]([\w.]+)/.exec( ua ) ||       
        /(trident)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
        ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
        [];
    return {
        browser: match[ 1 ] || "",
        version: match[ 2 ] || "0"
    };
};
matched = jQuery.uaMatch( navigator.userAgent );
//IE 11+ fix (Trident) 
matched.browser = matched.browser == 'trident' ? 'msie' : matched.browser;
browser = {};
if ( matched.browser ) {
    browser[ matched.browser ] = true;
    browser.version = matched.version;
}
// Chrome is Webkit, but Webkit is also Safari.
if ( browser.chrome ) {
    browser.webkit = true;
} else if ( browser.webkit ) {
    browser.safari = true;
}
jQuery.browser = browser;
// log removed - adds an extra dependency
//log(jQuery.browser)
$(function() {
	
		$( "#prod_name" ).focus();
		
		$('.product_category').livequery('change', function() {
		me = $(this);
		$(this).nextAll('.product_category').remove();
		if (me.hasClass("primary")){
			$('#show_sub_categories').html('');
		}
		
		var cat_id = $(this).val();
		var href=generateUrl('common', 'child_categories',[cat_id],webroot);
        callAjax(href,'', function(t){
			var ans = parseJsonData(t);
			if(ans === false || ans==""){
				return false
			}
			var response = '<select class="product_category" name="prod_category[]">'
			response=response+'<option value="">Select</option>';
			$.each(ans, function(key, value) {
				response=response+'<option value="'+key+'">'+value+'</option>';
       		});
			response = response+'</select>';
			$('#show_sub_categories').append(unescape(response));
			
		})
	});
	
    $(".detailTabs li a").click(function() {
    $(".tabs_content").hide();
    var activeTab = $(this).attr("rel"); 
    $("#"+activeTab).fadeIn();		
    $(".detailTabs li").removeClass("active");
    $(this).parent().addClass("active");
    });
	$('.detailTabs li a').last().addClass("tab_last");
	
	reloadImageTab($('#prod_id').val());
	
	$('input[name=\'shipping_country\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'countries_autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'shipping_country\']').val(suggestion.value);
				$('input[name=\'prod_shipping_country\']').val(suggestion.data);
        	 	//alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
    	 }
	});
	
	$('input[name=\'shipping_country\']').keyup(function(){
		//$('input[name=\'prod_shipping_country\']').val('');
	})
	
	
	$('input[name=\'brand_manufacturer\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'brands_autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'brand_manufacturer\']').val(suggestion.value);
				$('input[name=\'prod_brand\']').val(suggestion.data);
        	 	//alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
    	 }
	});
	
	
	$('input[name=\'brand_manufacturer\']').keyup(function(){
		//$('input[name=\'prod_brand\']').val('');
	})
	
	
	$('input[name=\'filter\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'filtergroupoptions_autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'filter\']').val('');
				$('#product-filter' + suggestion.data).remove();
				$('#product-filter').append('<div id="product-filter' + suggestion.data + '"><i class="remove_filter remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' +suggestion.value + '<input type="hidden" name="product_filter[]" value="' + suggestion.data + '" /></div>');
    	 }
	});
	
	$('#product-filter').delegate('.remove_filter', 'click', function() {
		$(this).parent().remove();
	});
	
	
	$('input[name=\'prod_tags\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'producttags_autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'prod_tags\']').val('');
				$('#product-tag' + suggestion.data).remove();
				$('#product-tag').append('<div id="product-tag' + suggestion.data + '"><i class="remove_tag remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' +suggestion.value + '<input type="hidden" name="product_tag[]" value="' + suggestion.data + '" /></div>');
    	 }
	});
	
	$('#product-tag').delegate('.remove_tag', 'click', function() {
		$(this).parent().remove();
	});
	
})
	
	function submitImageUploadImageForm(){
	if ($.browser.msie && parseInt($.browser.version, 10) === 8 || $.browser.msie && parseInt($.browser.version, 10) === 9) {
		$('#imagefrm').removeAttr('onsubmit')	 
		$('#imagefrm').submit(); return true; 
	}
	var data = new FormData();	 
	var $inputs = $('#frmProducts input[type=text],#frmProducts select,#frmProducts input[type=hidden]');
	$inputs.each(function() { data.append( this.name,$(this).val());}); 
	$.each($('#prod_image')[0].files, function(i, file) {
				var max_file_size = 1048576;
				var sizeinbytes = file.size;
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
				}
				
				if (sizeinbytes > max_file_size ){
					ShowJsSystemMessage(js_error_file_size.replace('{file_name}',file.name).replace('{file_size}',formatSizeUnits(max_file_size)),true,true);
				}else if (!blnValid){
					ShowJsSystemMessage(js_error_file_extensions.replace('{file_name}',file.name).replace('{allowed_extensions}',_validFileExtensions.join(", ")),true,true);
				}else{
					showHtmlElementLoading($('#imageupload_div'));
					$('.system_message').hide()
					data.append('prod_image', file);
					data.append( 'upload_mode' , 'ajax'); 
					$.ajax({ url: generateUrl('products', 'uploadProductImages' ), 
							data: data, 
							cache: false, 
							contentType: false, 
							processData: false, 
							type: 'POST', success: function(data){
								var t = jQuery.parseJSON(data);
								
								if (t.status!=1){
									$('.system_message').html('<a class="closeMsg" href="javascript:void(0);"></a>'+t.msg);
									$('.system_message').show();
									reloadImageTab($('#prod_id').val());
								}else{
									setTimeout(function(){reloadImageTab($('#prod_id').val())}, 2000);
								}
							} 
					});
				}
			}); 
			
	}


	function reloadImageTab(prod_tmp_id){
		callAjax(generateUrl('products', 'getImageUploadTab',[prod_tmp_id] ), '&outmode=json', function(t){	 
			$('#imageupload_div').html(t);
		}); 
	}
	
	 function deleteImage(image_id,product_id){ showHtmlElementLoading($('#imageupload_div')); callAjax(generateUrl('products', 'deleteImage', [image_id] ), '&outmode=json', function(data){	var t = jQuery.parseJSON(data); if(t.status == 0){alert(t.msg);}else{reloadImageTab( product_id );}});  
	 } 
	 
	 function setDefaultImage(el,product_id){ if($(el).val() == 0){ alert('Invalid input'); return } image_id = $(el).val(); if(! $(el).is(':checked') ){ 	var r=confirm( 'Set this image as main image?' ) 
	 	if (r==true){ callAjax(generateUrl('products', 'setDefaultImage', [image_id,product_id] ), '&outmode=json', function(data){  var t = jQuery.parseJSON(data); if(t.status == 0){ alert(t.msg); }else{ $(el).attr('checked', true); reloadImageTab( product_id ); } }); } } }
		
	function setMainImage(el, id, pid){
		id = parseInt(id);
		callAjax(generateUrl('products', 'setDefaultImage', [id,pid]), '&outmode=json', function(data){  var t = jQuery.parseJSON(data); if(t.status == 0){ alert(t.msg); }else{ reloadImageTab( pid ); } });
		return false;
	}
	
	function validateProductForm(frm,v){
		v.validate();
		if(!v.isValid()) {
			var activeTab = '';
			$('#frmProducts').each(function(){
			var validationFailedTab = $(this).find('.error').closest('.tabs_content').attr('id');	
			if(validationFailedTab.length > 0 ){activeTab = validationFailedTab;}
			});	
			$(".tabs_content").parents('.product_tabs').find(".tabs_content").hide();
			$("#"+activeTab).fadeIn();	
			$('a[rel="'+activeTab+'"]').parents('.product_tabs:first').find(".tabs_nav li a").removeClass("active");
			$('a[rel="'+activeTab+'"]').parents('.product_tabs:first').find(".tabs_nav li").removeClass("active");
			$('a[rel="'+activeTab+'"]').addClass('active');	
			return false;
		}
		
		var breakOut=false;
		data=$("#frmProducts").serialize();
		
		var href=generateUrl('common', 'min_price_criteria',[]);
		$.ajax({
				    url : href,
    				type: "POST",
				    data : data,
					async : false,
				    success: function(response, textStatus, jqXHR){
						 var ans = parseJsonData(response);
				         if (ans.error==1){
							$("#ajax_validation_message").html(ans.message); 
							$('html, body').animate({ scrollTop: 0 }, 'slow');
							//$('a[rel=tabs_1]').click();
							breakOut = true;
				 		}	
				    }
				});
		if(breakOut){
		   	return false;
		}
		
	}	
	
	function validateOptionForm(frm,v){
		HideJsSystemMessage();
		var me=$(this);
		if ( me.data('requestRunning') ) {
			return;
		}
		me.data('requestRunning', true);
				var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(logged) {
							if (logged==true){
								v.validate();
								if(!v.isValid()) {
									me.data('requestRunning', false);
									return;
								}
								var data = getFrmData(frm);
								data += '&outmode=json&is_ajax_request=yes';
								var href=generateUrl('account', 'option_form',['0','1']);
								callAjax(href, data, function(response){
									me.data('requestRunning', false);
									var json = parseJsonData(response);
									ShowJsSystemMessage(json['msg']);
									$("#frmOption").clearForm();
								})
								
							}else{  login_popupbox(); }
							me.data('requestRunning', false); 
					});
		return false;					
	}
$(document).on('click', 'ul.detailTabs li a', function(event) {
	event.preventDefault();
	$("#prod_tab").val($(this).attr("name"));
})
	