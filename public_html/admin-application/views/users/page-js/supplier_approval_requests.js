function searchSupplierApprovalRequests(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#supplier-approval_requests-list')); 
	callAjax(generateUrl('users', 'listSupplierApprovalRequests'), data, function(t){
		$('#supplier-approval_requests-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchSupplierApprovalRequests(frm);
}
$(document).ready(function(){
		searchSupplierApprovalRequests(document.frmSearchSupplierApprovalRequests);
});
  
function clearSearch() {
	document.frmSearchSupplierApprovalRequests.reset();
	$("#frmSearchSupplierApprovalRequests input[type=hidden]").val("");
	searchSupplierApprovalRequests(document.frmSearchSupplierApprovalRequests);
}
