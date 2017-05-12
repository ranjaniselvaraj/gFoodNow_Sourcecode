function searchBrowsingHistoryProducts(frm){ 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#browsinghistoryproducts-list')); 
	callAjax(generateUrl('smartrecommendations', 'listBrowsedHistoryProducts'), data, function(t){ 
		$('#browsinghistoryproducts-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchBrowsingHistoryProducts(frm);
}
$(document).ready(function(){
	searchBrowsingHistoryProducts(document.frmSearchProducts);
});
  
function clearSearch() {
	document.frmSearchProducts.reset();
	$("#frmSearchProducts input[type=hidden]").val("");
	searchBrowsingHistoryProducts(document.frmSearchProducts);
}
$(function() {
	
	
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
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'product_shop\']').val(suggestion.value);
				$('input[name=\'shop\']').val(suggestion.data);
        	 	
    	 }
	});
	
	
	$('input[name=\'product_shop\']').keyup(function(){
		$('input[name=\'shop\']').val('');
	})
	
	
	$('input[name=\'visitor\']').devbridgeAutocomplete({
			 minChars:0,
			// autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'users_autocomplete',[],webroot),
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
				$('input[name=\'visitor\']').val(suggestion.value);
				$('input[name=\'user\']').val(suggestion.data);
        	 	
    	 }
	});
	
	
	
	
})
