function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#post-type-list')); 
	callAjax(generateUrl('blogposts', 'listBlogPosts'), data, function(t){
		$('#post-type-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchPost(frm);
}
$(document).ready(function(){
	searchPost(document.frmPostSearch);
});
  
function clearSearch() {
	document.frmPostSearch.reset();
	$("#frmPostSearch input[type=hidden]").val("");
	searchPost(document.frmPostSearch);
}