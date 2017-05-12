function searchFilters(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#filters-list')); 
	callAjax(generateUrl('filters', 'listFilters'), data, function(t){
		$('#filters-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchFilters(frm);
}
$(document).ready(function(){
		searchFilters(document.frmFilterSearch);
});
  
function clearSearch() {
	document.frmFilterSearch.reset();
	$("#frmFilterSearch input[type=hidden]").val("");
	searchFilters(document.frmFilterSearch);
}

function ConfirmFilterDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('filters', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchFilters(document.frmFilterSearch);
		});
    });
    return false;
}
