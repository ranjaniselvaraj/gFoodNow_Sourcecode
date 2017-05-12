$(function() {
	
	$( "#product_shop_focus" ).focus();
		
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
				url: generateUrl('common', 'countries_autocomplete',[],webroot),
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
				url: generateUrl('common', 'brands_autocomplete',[],webroot),
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
	
	
	
	$('input[name=\'shop\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('shops', 'autocomplete'),
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
				$('input[name=\'shop\']').val(suggestion.value);
				$('input[name=\'prod_shop\']').val(suggestion.data);
        	 	
    	 }
	});
	
	
	$('input[name=\'shop\']').keyup(function(){
		//$('input[name=\'prod_shop\']').val('');
	})
	
	$('input[name=\'filter\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'filtergroupoptions_autocomplete',[],webroot),
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
	
	$('input[name=\'related\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'products_autocomplete',[],webroot),
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
				$('input[name=\'related\']').val('');
				$('#product-related' + suggestion.data).remove();
				$('#product-related').append('<div id="product-related' + suggestion.data + '"><i class="remove_related remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' +suggestion.value + '<input type="hidden" name="product_related[]" value="' + suggestion.data + '" /></div>');
    	 }
	});
	$('#product-related').delegate('.remove_related', 'click', function() {
		$(this).parent().remove();
	});
	
	
	$('input[name=\'prod_tags\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'producttags_autocomplete',[],webroot),
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
	function validateProductForm(frm,v){
		v.validate();
		if(!v.isValid()) {
			var activeTab = '';
			$('#frmProducts').each(function(){
			var validationFailedTab = $(this).find('.error').closest('.tabs_panel').attr('id');	
			if(validationFailedTab.length > 0 ){activeTab = validationFailedTab;}
			});	
			$(".tabs_panel").parents('.tabs_nav_container').find(".tabs_panel").hide();
			$("#"+activeTab).fadeIn();	
			$('a[rel="'+activeTab+'"]').parents('.tabs_nav_container:first').find(".tabs_nav li a").removeClass("active");
			$('a[rel="'+activeTab+'"]').parents('.tabs_nav_container:first').find(".tabs_nav li").removeClass("active");
			$('a[rel="'+activeTab+'"]').addClass('active');	
			return false;
		}
		
		var breakOut=false;
		data=$("#frmProducts").serialize();
		var href=generateUrl('common', 'min_price_criteria',[],webroot);
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
					$('.system_message').hide();
					data.append('prod_image', file);
					
					data.append( 'upload_mode' , 'ajax'); 
					$.ajax({ url: generateUrl('products', 'uploadProductImages' ), 
							data: data, 
							cache: false, 
							contentType: false, 
							processData: false, 
							type: 'POST', success: function(data){ //alert(data);
								var t = jQuery.parseJSON(data);
								if (t.status==1){
									setTimeout(function(){reloadImageTab($('#prod_id').val())}, 2000);
								}else{
									ShowJsSystemMessage(t.msg)
									reloadImageTab($('#prod_id').val());
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
		
	$(document).on('click', 'ul.detailTabs li a', function(event) {
		event.preventDefault();
		$("#prod_tab").val($(this).attr("name"));
	})	
	
	
	
 	