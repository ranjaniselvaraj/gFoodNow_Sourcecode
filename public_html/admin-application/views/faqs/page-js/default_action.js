function searchFAQs(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#faqs-list')); 
	callAjax(generateUrl('faqs', 'listfaqs'), data, function(t){
		$('#faqs-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchFAQs(frm);
}
$(document).ready(function(){
		searchFAQs(document.frmFAQSearch);
});
  
function clearSearch() {
	document.frmFAQSearch.reset();
	$("#frmFAQSearch input[type=hidden]").val("");
	searchFAQs(document.frmFAQSearch);
}

function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('faqs', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchFAQs(document.frmFAQSearch);
		});
    });
    return false;
}
