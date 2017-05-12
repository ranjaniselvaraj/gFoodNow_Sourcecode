function searchAffiliates(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#affiliates-list')); 
	callAjax(generateUrl('reports', 'listAffiliates'), data, function(t){ 
		$('#affiliates-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchAffiliates(frm);
}
$(document).ready(function(){
	searchAffiliates(document.frmAffiliateSearch);
});
  
function clearSearch() {
	document.frmAffiliateSearch.reset();
	$("#frmAffiliateSearch input[type=hidden]").val("");
	searchAffiliates(document.frmAffiliateSearch);
}
function exportRecords() {
	var frm = document.frmAffiliateSearch;
	frm.export.value = 'Y';
	document.frmAffiliateSearch.submit();
	
}
