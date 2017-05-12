function searchAttributes(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#attributes-list')); 
	callAjax(generateUrl('attributes', 'listAttributes'), data, function(t){
		$('#attributes-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchAttributes(frm);
}
$(document).ready(function(){
		searchAttributes(document.frmSearchAttribute);
});
  
function clearSearch() {
	document.frmSearchAttribute.reset();
	$("#frmSearchAttribute input[type=hidden]").val("");
	searchAttributes(document.frmSearchAttribute);
}
function ConfirmAttributeDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('attributes', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchAttributes(document.frmSearchAttribute);
		});
    });
    return false;
}
