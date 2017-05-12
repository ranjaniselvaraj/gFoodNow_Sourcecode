function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#category-post-list'));
	callAjax(generateUrl('blog', 'listCatPost'), data, function(t){
		$('#category-post-list').html(t);
	});
}
function listPages(p){
	var frm = document.frmPaging;
	frm.page.value = p;
	searchPost(frm);
}
/* $(document).ready(function(){
	showHtmlElementLoading($('#category-post-list'));
	var data = 'page=1';
	callAjax(Utilities::generateUrl('blog', 'listCatPost'), data, function(t){
		$('#category-post-list').html(t);
	});
	//searchPost(document.frmPaging);
}); */
$(document).ready(function(){
	searchPost(document.frmCategory);
});