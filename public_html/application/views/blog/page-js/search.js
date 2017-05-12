function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#search-post-list'));
	callAjax(generateUrl('blog', 'searchlist'), data, function(t){
		$('#search-post-list').html(t);
	});
}
function listPages(p){
	var frm = document.frmPaging;
	frm.page.value = p;
	searchPost(frm);
}
$(document).ready(function(){
	searchPost(document.frmSearchPost);
});