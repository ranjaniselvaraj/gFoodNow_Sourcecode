function searchWithdrawalRequests(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#withdrawalrequests-list')); 
	callAjax(generateUrl('custom', 'listWithdrawalRequests'), data, function(t){ 
		$('#withdrawalrequests-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchWithdrawalRequests(frm);
}
$(document).ready(function(){
		searchWithdrawalRequests(document.frmWithdrawalRequestSearch);
});
  
function clearSearch() {
	document.frmWithdrawalRequestSearch.reset();
	$("#frmWithdrawalRequestSearch input[type=hidden]").val("");
	searchWithdrawalRequests(document.frmWithdrawalRequestSearch);
}
function UpdateRequestStatus(id, el, mod) {
	callAjax(generateUrl('custom', 'update_withdrawal_request_status'), 'id=' + id+'&mod=' + mod, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		$(el).closest("td").remove()
	});
}
