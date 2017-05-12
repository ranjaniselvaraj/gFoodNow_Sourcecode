$(document).ready(function() {
	searchFavorites(document.frmSearch);
});
function listPagingFavorites(p){
	var frm = document.frmSearch;
	frm.page.value = p;
	searchFavorites(frm);
}
function searchFavorites(frm){
	var data = getFrmData(frm);
	//showCssElementLoading($('#favorite-list'),1);
	callAjax(generateUrl('shops', 'ajax_show_favorite_list'), data, function(t){
		$(".loadmorepage").html('').hide();
		$('#favorite-list').append(t);
	});
}
