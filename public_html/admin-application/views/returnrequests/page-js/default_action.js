function searchReturnRequests(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#returnrequests-list')); 
	callAjax(generateUrl('returnrequests', 'listReturnRequests'), data, function(t){
		$('#returnrequests-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchReturnRequests(frm);
}
$(document).ready(function(){
		searchReturnRequests(document.frmReturnRequestsSearch);
});
  
function clearSearch() {
	document.frmReturnRequestsSearch.reset();
	$("#frmReturnRequestsSearch input[type=hidden]").val("");
	searchReturnRequests(document.frmReturnRequestsSearch);
}
function UpdateRequestStatus(id, el, mod) {
	callAjax(generateUrl('returnrequests', 'update_request_status'), 'id=' + id+'&mod=' + mod, function(t){ 
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		$(el).closest("li").remove()
		
	});
}
