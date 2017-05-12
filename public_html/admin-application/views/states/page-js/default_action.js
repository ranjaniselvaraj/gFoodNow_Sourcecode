function searchStates(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#states-list')); 
	callAjax(generateUrl('states', 'listStates'), data, function(t){
		$('#states-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchStates(frm);
}
$(document).ready(function(){
		searchStates(document.frmStateSearch);
});
  
function clearSearch() {
	document.frmStateSearch.reset();
	$("#frmStateSearch input[type=hidden]").val("");
	searchStates(document.frmStateSearch);
}
function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('states', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchStates(document.frmStateSearch);
		});
    });
    return false;
	
}
