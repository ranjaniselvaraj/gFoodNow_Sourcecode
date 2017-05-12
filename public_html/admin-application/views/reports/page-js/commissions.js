function searchCommissions(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#commissions-list')); 
	callAjax(generateUrl('reports', 'listCommissions'), data, function(t){ 
		$('#commissions-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchCommissions(frm);
}
$(document).ready(function(){
	searchCommissions(document.frmCommissionsSearch);
});
  
function clearSearch() {
	document.frmCommissionsSearch.reset();
	$("#frmCommissionsSearch input[type=hidden]").val("");
	searchCommissions(document.frmCommissionsSearch);
}
function exportRecords() {
	var frm = document.frmCommissionsSearch;
	frm.export.value = 'Y';
	document.frmCommissionsSearch.submit();
	
}
