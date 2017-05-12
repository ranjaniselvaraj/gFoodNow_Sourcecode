function searchAffiliateWithdrawalRequests(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#withdrawal-requests-list')); 
	callAjax(generateUrl('affiliates', 'listWithdrawalRequests'), data, function(t){
		$('#withdrawal-requests-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchAffiliateWithdrawalRequests(frm);
}
$(document).ready(function(){
		searchAffiliateWithdrawalRequests(document.frmSearchAffiliateWithdrawalRequests);
});
  
function clearSearch() {
	document.frmSearchAffiliateWithdrawalRequests.reset();
	$("#frmSearchAffiliateWithdrawalRequests input[type=hidden]").val("");
	searchAffiliateWithdrawalRequests(document.frmSearchAffiliateWithdrawalRequests);
}
function UpdateAffiliateWithdrawalRequestStatus(id, el,mod) {
	callAjax(generateUrl('affiliates', 'update_affiliate_withdrawal_request_status'), 'id=' + id+'&mod='+mod, function(t){
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
