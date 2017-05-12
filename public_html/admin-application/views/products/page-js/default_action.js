$(function() {
	
	$('input[name=\'brand_manufacturer\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'brands_autocomplete','',webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { 
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'brand_manufacturer\']').val(suggestion.value);
				$('input[name=\'brand\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'brand_manufacturer\']').keyup(function(){
		$('input[name=\'brand\']').val('');
	})
	
	
	$('input[name=\'product_shop\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('shops', 'autocomplete'),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { 
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'shop\']').val(suggestion.data);
				$('input[name=\'product_shop\']').val(suggestion.value);
    	 }
	});
	
	$('input[name=\'product_shop\']').keyup(function(){
		$('input[name=\'shop\']').val('');
	})
	
	
	
})
function searchProducts(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#products-list')); 
	callAjax(generateUrl('products', 'listProducts'), data, function(t){
		$('#products-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchProducts(frm);
}
$(document).ready(function(){
		searchProducts(document.frmSearchProducts);
});
  
function clearSearch() {
	document.frmSearchProducts.reset();
	$("#frmSearchProducts input[type=hidden]").val("");
	searchProducts(document.frmSearchProducts);
}
function UpdateProductStatus(id, el) {
	callAjax(generateUrl('products', 'update_product_status'), 'id=' + id, function(t){ 
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		if (el.parent().hasClass('enabled')) {
			el.parent().removeClass('enabled').addClass('disabled');
		}else {
			el.parent().removeClass('disabled').addClass('enabled');
		}
		el.parent().attr("title", "Click to Disable");
		if (el.parent().hasClass('disabled')) {
			el.parent().attr("title", "Click to Enable");
		}	
	});
}
function ConfirmProductDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('products', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchProducts(document.frmSearchProducts);
		});
    });
    return false;
}
