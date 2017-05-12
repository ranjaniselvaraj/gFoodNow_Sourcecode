function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#comment-list'));
	callAjax(generateUrl('blogcomments', 'listComments'), data, function(t){
		$('#comment-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchPost(frm);
}
$(document).ready(function(){
	searchPost(document.frmComment);
});
function clearSearch() {
	document.frmComment.reset();
	$("#frmComment input[type=hidden]").val("");
	searchPost(document.frmComment);
}