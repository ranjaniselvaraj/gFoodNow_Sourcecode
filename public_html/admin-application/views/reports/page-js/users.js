function searchUsers(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#users-list')); 
	callAjax(generateUrl('reports', 'listUsers'), data, function(t){ 
		$('#users-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchUsers(frm);
}
$(document).ready(function(){
	searchUsers(document.frmUsersSearch);
});
  
function clearSearch() {
	document.frmUsersSearch.reset();
	$("#frmUsersSearch input[type=hidden]").val("");
	searchUsers(document.frmUsersSearch);
}
function exportRecords() {
	var frm = document.frmUsersSearch;
	frm.export.value = 'Y';
	document.frmUsersSearch.submit();
	
}
