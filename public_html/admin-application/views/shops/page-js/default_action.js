function searchShops(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#shops-list')); 
	callAjax(generateUrl('shops', 'listShops'), data, function(t){
		$('#shops-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchShops(frm);
}
$(document).ready(function(){
		searchShops(document.frmShopSearch);
});
  
function clearSearch() {
	document.frmShopSearch.reset();
	$("#frmShopSearch input[type=hidden]").val("");
	searchShops(document.frmShopSearch);
}
function UpdateShopStatus(id, el) {
	callAjax(generateUrl('shops', 'update_shop_status'), 'id=' + id, function(t){
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

function ConfirmShopDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('shops', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchShops(document.frmShopSearch);
		});
    });
    return false;
}
