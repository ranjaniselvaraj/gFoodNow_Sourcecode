function searchAdvertisers(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#advertisers-list')); 
	callAjax(generateUrl('users', 'listAdvertisers'), data, function(t){
		$('#advertisers-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchAdvertisers(frm);
}
$(document).ready(function(){
		searchAdvertisers(document.frmAdvertiserSearch);
});
  
function clearSearch() {
	document.frmAdvertiserSearch.reset();
	$("#frmAdvertiserSearch input[type=hidden]").val("");
	searchAdvertisers(document.frmAdvertiserSearch);
}
function UpdateAdvertiserStatus(id, el) {
	callAjax(generateUrl('users', 'update_user_status'), 'id=' + id, function(t){
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
function ConfirmAdvertiserDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('users', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchAdvertisers(document.frmAdvertiserSearch);
		});
    });
    return false;
}
