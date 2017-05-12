function searchAdvertisers(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#advertisers-list')); 
	callAjax(generateUrl('reports', 'listAdvertisers'), data, function(t){ 
		$('#advertisers-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchAdvertisers(frm);
}
$(document).ready(function(){
	searchAdvertisers(document.frmAdvertisersSearch);
});
  
function clearSearch() {
	document.frmAdvertisersSearch.reset();
	$("#frmAdvertisersSearch input[type=hidden]").val("");
	searchAdvertisers(document.frmAdvertisersSearch);
}
function exportRecords() {
	var frm = document.frmAdvertisersSearch;
	frm.export.value = 'Y';
	document.frmAdvertisersSearch.submit();
	
}
