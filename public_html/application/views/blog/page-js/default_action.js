function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#post-list'));
	callAjax(generateUrl('blog', 'listPost'), data, function(t){
		$('#post-list').html(t);
		$("body, html").animate({
			scrollTop: $('#post-list').position().top-20
		});
	});
}
function listPages(p){
	var frm = document.frmPaging;
	frm.page.value = p;
	searchPost(frm);
}
$(document).ready(function(){
	showHtmlElementLoading($('#post-list'));
	var data = 'page=1';
	callAjax(generateUrl('blog', 'listPost'), data, function(t){
		$('#post-list').html(t);
	});
	/*searchPost(document.frmPaging);*/
});