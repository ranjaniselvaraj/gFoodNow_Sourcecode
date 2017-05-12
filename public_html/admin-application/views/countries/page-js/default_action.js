function searchCountries(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#countries-list')); 
	callAjax(generateUrl('countries', 'listCountries'), data, function(t){
		$('#countries-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchCountries(frm);
}
$(document).ready(function(){
		searchCountries(document.frmCountrySearch);
});
  
function clearSearch() {
	document.frmCountrySearch.reset();
	$("#frmCountrySearch input[type=hidden]").val("");
	searchCountries(document.frmCountrySearch);
}
function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('countries', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchCountries(document.frmCountrySearch);
		});
    });
    return false;
}
