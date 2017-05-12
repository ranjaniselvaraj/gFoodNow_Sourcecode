function searchBrands(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#brands-list')); 
	callAjax(generateUrl('brands', 'listBrands'), data, function(t){
		$('#brands-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchBrands(frm);
}
$(document).ready(function(){
		searchBrands(document.frmBrandSearch);
});
  
function clearSearch() {
	document.frmBrandSearch.reset();
	$("#frmBrandSearch input[type=hidden]").val("");
	searchBrands(document.frmBrandSearch);
}
function UpdateBrandStatus(id, el) {
	callAjax(generateUrl('brands', 'update_brand_status'), 'id=' + id, function(t){
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


function ConfirmBrandDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('brands', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchBrands(document.frmBrandSearch);
		});
    });
    return false;
}
