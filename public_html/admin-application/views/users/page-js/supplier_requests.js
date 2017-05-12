function searchSupplierRequests(frm){
	
	var data = getFrmData(frm);
	showHtmlElementLoading($('#supplier_requests-list')); 
	callAjax(generateUrl('users', 'listSupplierRequests'), data, function(t){
		$('#supplier_requests-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchSupplierRequests(frm);
}
$(document).ready(function(){
		searchSupplierRequests(document.frmSearchSupplierRequests);
});
  
function clearSearch() {
	document.frmSearchSupplierRequests.reset();
	$("#frmSearchSupplierRequests input[type=hidden]").val("");
	searchSupplierRequests(document.frmSearchSupplierRequests);
}
function UpdateRequestStatus(id, el, mod) {
	callAjax(generateUrl('users', 'update_supplier_request_status'), 'id=' + id+'&mod=' + mod, function(t){
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
