function searchPromotions(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#promotions-list')); 
	callAjax(generateUrl('reports', 'listPromotions'), data, function(t){ 
		$('#promotions-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchPromotions(frm);
}
$(document).ready(function(){ 
	searchPromotions(document.frmPromotionsSearch);
});
  
function clearSearch() {
	document.frmPromotionsSearch.reset();
	$("#frmPromotionsSearch input[type=hidden]").val("");
	searchPromotions(document.frmPromotionsSearch);
}
function exportRecords() {
	var frm = document.frmPromotionsSearch;
	frm.export.value = 'Y';
	document.frmPromotionsSearch.submit();
	
}

$(function() {
	$('input[name=\'promotion_by\']').devbridgeAutocomplete({
			 minChars:0,
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('ppc', 'promoters_autocomplete',[1]),
				data: {keyword: encodeURIComponent(query)},
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				 $('input[name=\'promotion_by\']').val(suggestion.value);
				 $('input[name=\'user\']').val(suggestion.data);
			 }
	});
})
