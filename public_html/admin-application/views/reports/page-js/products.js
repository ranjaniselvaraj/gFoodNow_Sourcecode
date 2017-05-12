function searchProducts(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#products-list')); 
	callAjax(generateUrl('reports', 'listProducts'), data, function(t){ 
		$('#products-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchProducts(frm);
}
$(document).ready(function(){ 
	searchProducts(document.frmProductsSearch);
});
  
function clearSearch() {
	document.frmProductsSearch.reset();
	$("#frmProductsSearch input[type=hidden]").val("");
	searchProducts(document.frmProductsSearch);
}
function exportRecords() {
	var frm = document.frmProductsSearch;
	frm.export.value = 'Y';
	document.frmProductsSearch.submit();
	
}
