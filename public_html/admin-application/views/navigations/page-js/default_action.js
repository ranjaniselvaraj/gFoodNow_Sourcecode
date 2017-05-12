function searchNavigations(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#navigations-list')); 
	callAjax(generateUrl('navigations', 'listNavigations'), data, function(t){ 
		$('#navigations-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchNavigations(frm);
}
$(document).ready(function(){
		searchNavigations(document.frmNavigationSearch);
});
  
function clearSearch() {
	document.frmNavigationSearch.reset();
	$("#frmNavigationSearch input[type=hidden]").val("");
	searchNavigations(document.frmNavigationSearch);
}
function UpdateNavigationStatus(id, el) {
	callAjax(generateUrl('navigations', 'update_navigation_status'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		if (el.parent().hasClass('enabled')) {
			el.parent().removeClass('enabled').addClass('disabled');
		}else {
			el.parent().removeClass('disabled').addClass('enabled');
		}
		el.parent().attr("title", "Click to Disable");
		if (el.parent().hasClass('disabled')) {
			el.parent().attr("title", "Click to Enable");
		}	
	});
}
