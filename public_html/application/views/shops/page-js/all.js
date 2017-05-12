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
	callAjax(generateUrl('shops', 'ajax_show_shops'), data, function(t){
		$(".loadmorepage").html('').hide();
		$('#shops-list').append(t);
		equalHeight($(".equal_height_shop_item"));
	});
}
$(window).resize(function(){
	equalHeight($(".equal_height_shop_item"));
});