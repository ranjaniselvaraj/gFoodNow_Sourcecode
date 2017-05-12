function searchUsers(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#users-list')); 
	callAjax(generateUrl('users', 'listUsers'), data, function(t){
		$('#users-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchUsers(frm);
}
$(document).ready(function(){
		searchUsers(document.frmUserSearch);
});
  
function clearSearch() {
	document.frmUserSearch.reset();
	searchUsers(document.frmUserSearch);
}
function RestoreDeletedUser(id, el) {
	callAjax(generateUrl('users', 'restore_deleted_user'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		searchUsers(document.frmUserSearch);
	});
}
