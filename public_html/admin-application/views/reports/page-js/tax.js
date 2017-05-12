function searchTax(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#tax-list')); 
	callAjax(generateUrl('reports', 'listTax'), data, function(t){ 
		$('#tax-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchTax(frm);
}
$(document).ready(function(){ 
	searchTax(document.frmTaxSearch);
});
  
function clearSearch() {
	document.frmTaxSearch.reset();
	$("#frmTaxSearch input[type=hidden]").val("");
	searchTax(document.frmTaxSearch);
}
function exportRecords() {
	var frm = document.frmTaxSearch;
	frm.export.value = 'Y';
	document.frmTaxSearch.submit();
	
}
