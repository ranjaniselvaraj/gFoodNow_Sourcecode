function searchSales(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#sales-list')); 
	callAjax(generateUrl('reports', 'listSales'), data, function(t){ 
		$('#sales-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchSales(frm);
}
$(document).ready(function(){
	searchSales(document.frmSalesSearch);
});
  
function clearSearch() {
	document.frmSalesSearch.reset();
	$("#frmSalesSearch input[type=hidden]").val("");
	searchSales(document.frmSalesSearch);
}
function exportRecords() {
	var frm = document.frmSalesSearch;
	frm.export.value = 'Y';
	document.frmSalesSearch.submit();
	
}
