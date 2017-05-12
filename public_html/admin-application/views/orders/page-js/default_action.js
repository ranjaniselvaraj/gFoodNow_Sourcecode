function searchOrders(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#orders-list')); 
	callAjax(generateUrl('orders', 'listOrders'), data, function(t){
		$('#orders-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchOrders(frm);
}
$(document).ready(function(){
		searchOrders(document.frmOrderSearch);
});
  
function clearSearch() {
	document.frmOrderSearch.reset();
	$("#frmOrderSearch input[type=hidden]").val("");
	searchOrders(document.frmOrderSearch);
}

function ConfirmOrderCancel(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('orders', 'cancel'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchOrders(document.frmOrderSearch);
		});
    });
    return false;
}
$(function() {
	
	$('input[name=\'customer_name\']').devbridgeAutocomplete({
			 minChars:0,
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('orders', 'customers_autocomplete',[1]),
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
				$('input[name=\'customer_name\']').val(suggestion.value);
				$('input[name=\'user\']').val(suggestion.data);
	    	 }
	});
	
	
	
	
})
