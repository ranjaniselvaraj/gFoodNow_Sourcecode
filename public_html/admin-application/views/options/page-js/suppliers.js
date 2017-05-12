function searchOptions(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#options-list')); 
	callAjax(generateUrl('options', 'listOptions'), data, function(t){
		$('#options-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchOptions(frm);
}
$(document).ready(function(){
		searchOptions(document.frmSearchOption);
});
  
function clearSearch() {
	document.frmSearchOption.reset();
	$("#frmSearchOption input[type=hidden]").val("");
	searchOptions(document.frmSearchOption);
}
function ConfirmOptionDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('options', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchOptions(document.frmSearchOption);
		});
    });
    return false;
}
