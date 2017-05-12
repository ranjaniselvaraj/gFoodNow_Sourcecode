function searchUserBalance(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#user-balance-list')); 
	callAjax(generateUrl('users', 'listUserBalanceRecords'), data, function(t){
		$('#user-balance-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchUserBalance(frm);
}
$(document).ready(function(){
		searchUserBalance(document.frmUserBalanceSearch);
});
  
function clearSearch() {
	document.frmUserBalanceSearch.reset();
	$("#frmUserBalanceSearch input[type=hidden]").val("");
	searchUserBalance(document.frmUserBalanceSearch);
}
