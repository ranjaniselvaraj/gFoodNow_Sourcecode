function searchSubscriptions(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#subscriptions-list')); 
	callAjax(generateUrl('reports', 'listSubscriptions'), data, function(t){ 
		$('#subscriptions-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchSubscriptions(frm);
}
$(document).ready(function(){ 
	searchSubscriptions(document.frmSubscriptionsSearch);
});
  
function clearSearch() {
	document.frmSubscriptionsSearch.reset();
	$("#frmSubscriptionsSearch input[type=hidden]").val("");
	searchSubscriptions(document.frmSubscriptionsSearch);
}
function exportRecords() {
	var frm = document.frmSubscriptionsSearch;
	frm.export.value = 'Y';
	document.frmSubscriptionsSearch.submit();
	
}

$(function() {
	$('input[name=\'subscriber_name\']').devbridgeAutocomplete({
			 minChars:0,
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('subscriptionorders', 'subscribers_autocomplete',[1]),
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
				$('input[name=\'subscriber_name\']').val(suggestion.value);
				$('input[name=\'subscriber\']').val(suggestion.data);
	    	 }
	});
})