function searchCoupons(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#coupons-list')); 
	callAjax(generateUrl('coupons', 'listCoupons'), data, function(t){
		$('#coupons-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchCoupons(frm);
}
$(document).ready(function(){
		searchCoupons(document.frmCouponSearch);
});
  
function clearSearch() {
	document.frmCouponSearch.reset();
	$("#frmCouponSearch input[type=hidden]").val("");
	searchCoupons(document.frmCouponSearch);
}
function UpdateCouponStatus(id, el) {
	callAjax(generateUrl('coupons', 'update_coupon_status'), 'id=' + id, function(t){
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
		callAjax(generateUrl('coupons', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchCoupons(document.frmCouponSearch);
		});
    });
    return false;
}
