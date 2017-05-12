function UpdateBannerStatus(id, el) {
	callAjax(generateUrl('banners', 'update_banner_status'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		//alert(ans.msg);
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
function ConfirmBannerDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('banners', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			$(el).closest("tr").remove()
		});
    });
    return false;
}
