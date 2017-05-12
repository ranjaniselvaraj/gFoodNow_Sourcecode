$(document).ready(function() {
	searchShops(document.primarySearchForm);
});
function listPagingShops(p){
	var frm = document.primarySearchForm;
	frm.page.value = p;
	searchShops(frm);
}
function searchShops(frm){
	var data = getFrmData(frm);
	//showCssElementLoading($('#shops-list'),1);
	callAjax(generateUrl('shops', 'ajax_show_shops'), data, function(t){
		$(".loadmorepage").html('').hide();
		$('#shops-list').append(t);
		//setTimeout(function(){ equalHeight($(".equal_height_shop_item")) }, 1000);
		equalHeight($(".equal_height_shop_item"));
		
	});
}
