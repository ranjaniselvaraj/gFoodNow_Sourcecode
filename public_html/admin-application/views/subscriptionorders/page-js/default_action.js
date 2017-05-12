function searchSubscriptionOrders(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#subscription-orders-list')); 
	callAjax(generateUrl('subscriptionorders', 'listsubscriptionorders'), data, function(t){
		$('#subscription-orders-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchSubscriptionOrders(frm);
}
$(document).ready(function(){
		searchSubscriptionOrders(document.frmSubscriptionOrderSearch);
});
  
function clearSearch() {
	document.frmSubscriptionOrderSearch.reset();
	searchSubscriptionOrders(document.frmSubscriptionOrderSearch);
}
function ConfirmSubscriptionCancel() {
	var sure = confirm('Are you sure you want to cancel this order?');
    if (!sure) {
      return;
	}
	
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

