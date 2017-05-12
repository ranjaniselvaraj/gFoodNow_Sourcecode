function searchContributions(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#contributions-type-list'));
	callAjax(generateUrl('blogcontributions', 'listContributions'), data, function(t){
		$('#contributions-type-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchContributions(frm);
}
$(document).ready(function(){
	searchContributions(document.frmSearchContri);
});
function clearSearch() {
	document.frmSearchContri.reset();
	$("#frmSearchContri input[type=hidden]").val("");
	searchContributions(document.frmSearchContri);
}