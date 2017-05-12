function searchCollections(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#collections-list')); 
	callAjax(generateUrl('collections', 'listCollections'), data, function(t){
		$('#collections-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchCollections(frm);
}
$(document).ready(function(){
		searchCollections(document.frmCollectionSearch);
});
  
function clearSearch() {
	document.frmCollectionSearch.reset();
	$("#frmCollectionSearch input[type=hidden]").val("");
	searchCollections(document.frmCollectionSearch);
}
function UpdateCollectionStatus(id, el) {
	callAjax(generateUrl('collections', 'update_collection_status'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		if (el.parent().hasClass('enabled')) {
			el.parent().removeClass('enabled').addClass('disabled');
		}else {
			el.parent().removeClass('disabled').addClass('enabled');
		}
		el.parent().attr("title", "Click to Disable");
		if (el.parent().hasClass('disabled')) {
			el.parent().attr("title", "Click to Enable");
		}
	});
}
function ConfirmCollectionDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('collections', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchCollections(document.frmCollectionSearch);
		});
    });
    return false;
}
