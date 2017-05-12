function searchShops(frm){
	frm.export.value=''; 
	var data = getFrmData(frm);
	showHtmlElementLoading($('#shops-list')); 
	callAjax(generateUrl('reports', 'listShops'), data, function(t){ 
		$('#shops-list').html(t);
		applyRating();
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchShops(frm);
}
$(document).ready(function(){ 
	searchShops(document.frmShopsSearch);
});
  
function clearSearch() {
	document.frmShopsSearch.reset();
	$("#frmShopsSearch input[type=hidden]").val("");
	searchShops(document.frmShopsSearch);
}
function exportRecords() {
	var frm = document.frmShopsSearch;
	frm.export.value = 'Y';
	document.frmShopsSearch.submit();
	
}
function applyRating(){
	$('.ratings').raty({
			score: function() {
			return $(this).attr('data-score');
		},
		readOnly: true,
		space : false,
		width : 100,
	});
}
