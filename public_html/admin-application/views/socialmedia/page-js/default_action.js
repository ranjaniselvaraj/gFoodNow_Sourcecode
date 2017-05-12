function seachSocialPlatforms(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#socialmedia-list')); 
	callAjax(generateUrl('socialmedia', 'listsocialplatforms'), data, function(t){ 
		$('#socialmedia-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	seachSocialPlatforms(frm);
}
$(document).ready(function(){
		seachSocialPlatforms(document.frmSocialPlatformSearch);
});
  
function clearSearch() {
	document.frmSocialPlatformSearch.reset();
	$("#frmSocialPlatformSearch input[type=hidden]").val("");
	seachSocialPlatforms(document.frmSocialPlatformSearch);
}
function UpdateSocialPlatformStatus(id, el) {
	callAjax(generateUrl('socialmedia', 'update_social_platform_status'), 'id=' + id, function(t){
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
function ConfirmSocialPlatformDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('socialmedia', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			seachSocialPlatforms(document.frmSocialPlatformSearch);
		});
    });
    return false;
	
}
