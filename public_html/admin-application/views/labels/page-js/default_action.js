function searchLabels(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#labels-list')); 
	callAjax(generateUrl('labels', 'listLabels'), data, function(t){
		$('#labels-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchLabels(frm);
}
$(document).ready(function(){
		searchLabels(document.frmSearchLabels);
});
  
function clearSearch() {
	document.frmSearchLabels.reset();
	$("#frmSearchLabels input[type=hidden]").val("");
	searchLabels(document.frmSearchLabels);
}
