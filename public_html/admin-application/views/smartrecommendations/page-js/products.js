function searchRecommendedProducts(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#recommendedproducts-list')); 
	callAjax(generateUrl('smartrecommendations', 'listRecommendedProducts'), data, function(t){
		$('#recommendedproducts-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchRecommendedProducts(frm);
}
$(document).ready(function(){
		searchRecommendedProducts(document.frmSearchProducts);
});
  
function clearSearch() {
	document.frmSearchProducts.reset();
	$("#frmSearchProducts input[type=hidden]").val("");
	searchRecommendedProducts(document.frmSearchProducts);
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
	
	
	
})
function saveData(Id, txtObj,ColName){
		 var newValue = txtObj.value;
		 var mySplitResult = ColName.split("_");
	  	 callAjax(generateUrl("smartrecommendations", "updateProductRecommendations"), 'id='+Id+'&value='+newValue+'&field='+ColName, function(t)
		 {
			 $('#'+ColName+'-ajax-'+Id).html('Saved').fadeIn().delay(1000).fadeOut();
			
		});
	 }
