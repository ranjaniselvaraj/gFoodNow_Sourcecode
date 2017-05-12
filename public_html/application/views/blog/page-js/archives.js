function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#archives-post-list'));
	callAjax(generateUrl('blog', 'listArchivesPost'), data, function(t){
		$('#archives-post-list').html(t);
	});
}
function listPages(p){
	var frm = document.frmPaging;
	frm.page.value = p;
	searchPost(frm);
}
$(document).ready(function(){
	searchPost(document.frmArchives);
});