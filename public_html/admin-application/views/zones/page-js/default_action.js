function searchZones(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#zones-list')); 
	callAjax(generateUrl('zones', 'listZones'), data, function(t){
		$('#zones-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchZones(frm);
}
$(document).ready(function(){
		searchZones(document.frmZoneSearch);
});
  
function clearSearch() {
	document.frmZoneSearch.reset();
	$("#frmZoneSearch input[type=hidden]").val("");
	searchZones(document.frmZoneSearch);
}
function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('zones', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchZones(document.frmZoneSearch);
		});
    });
    return false;
	
}
