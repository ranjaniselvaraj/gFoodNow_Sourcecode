function searchPages(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#pages-list')); 
	callAjax(generateUrl('cms', 'listPages'), data, function(t){
		$('#pages-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchPages(frm);
}
$(document).ready(function(){
		searchPages(document.frmCmsPagesSearch);
});
  
function clearSearch() {
	document.frmCmsPagesSearch.reset();
	$("#frmCmsPagesSearch input[type=hidden]").val("");
	searchPages(document.frmCmsPagesSearch);
}
function ConfirmCmsPageDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('cms', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchPages(document.frmCmsPagesSearch);
		});
    });
    return false;
}
