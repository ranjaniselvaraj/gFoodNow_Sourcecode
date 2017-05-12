function searchSubscriptionCoupons(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#subscription-coupons-list')); 
	callAjax(generateUrl('subscriptioncoupons', 'listsubscriptioncoupons'), data, function(t){
		$('#subscription-coupons-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchSubscriptionCoupons(frm);
}
$(document).ready(function(){
		searchSubscriptionCoupons(document.frmSubscriptionCouponSearch);
});
  
function clearSearch() {
	document.frmCouponSearch.reset();
	$("#frmSubscriptionCouponSearch input[type=hidden]").val("");
	searchSubscriptionCoupons(document.frmCouponSearch);
}
function UpdateCouponStatus(id, el) {
	callAjax(generateUrl('subscriptioncoupons', 'update_coupon_status'), 'id=' + id, function(t){
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
function ConfirmCouponDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('subscriptioncoupons', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchSubscriptionCoupons(document.frmSubscriptionCouponSearch);
		});
    });
    return false;
}
