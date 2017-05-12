function searchCategories(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#categories-list')); 
	callAjax(generateUrl('categories', 'listCategories'), data, function(t){
		$('#categories-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchCategories(frm);
}
function showChildCategories(parent){
	var frm = document.paginateForm;
	frm.parent.value = parent;
	searchCategories(frm);
}
$(document).ready(function(){
		searchCategories(document.frmCategoriesSearch);
});
  
function clearSearch() {
	document.frmCategoriesSearch.reset();
	//$("#frmCategoriesSearch input[type=hidden]").val("");
	searchCategories(document.frmCategoriesSearch);
}
function UpdateCategoryStatus(id, el) {
	callAjax(generateUrl('categories', 'update_category_status'), 'id=' + id, function(t){
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
function ConfirmCategoryDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('categories', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchCategories(document.frmCategoriesSearch);
		});
    });
    return false;
}
