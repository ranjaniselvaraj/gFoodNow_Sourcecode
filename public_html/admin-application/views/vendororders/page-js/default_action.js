function searchVendorOrders(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#vendororders-list')); 
	callAjax(generateUrl('vendororders', 'listVendorOrders'), data, function(t){ 
		$('#vendororders-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchVendorOrders(frm);
}
$(document).ready(function(){
		searchVendorOrders(document.frmVendorOrderSearch);
});
  
function clearSearch() {
	document.frmVendorOrderSearch.reset();
	$("#frmVendorOrderSearch input[type=hidden]").val("");
	searchVendorOrders(document.frmVendorOrderSearch);
}
$(function() {
	
	$('input[name=\'order_customer_name\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('orders', 'customers_autocomplete',[1]),
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
				$('input[name=\'order_customer_name\']').val(suggestion.value);
				$('input[name=\'customer\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'shop_name\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('vendororders', 'shops_autocomplete',[1]),
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
				$('input[name=\'shop_name\']').val(suggestion.value);
    	 }
	});
	
	
})
