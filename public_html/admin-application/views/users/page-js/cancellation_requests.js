function searchCancellationRequests(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#cancellation-requests-list')); 
	callAjax(generateUrl('users', 'listCancellationRequests'), data, function(t){
		$('#cancellation-requests-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchCancellationRequests(frm);
}
$(document).ready(function(){
		searchCancellationRequests(document.frmCancellationRequests);
});
  
function clearSearch() {
	document.frmCancellationRequests.reset();
	$("#frmCancellationRequests input[type=hidden]").val("");
	searchCancellationRequests(document.frmCancellationRequests);
}
function UpdateRequestStatus(id, el, mod) {
	callAjax(generateUrl('users', 'update_user_cancellation_request_status'), 'id=' + id+'&mod=' + mod, function(t){
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
