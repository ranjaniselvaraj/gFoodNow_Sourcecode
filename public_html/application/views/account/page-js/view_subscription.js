$("document").ready(function(){
	
});
function listPages(p){
	var frm = document.frmSearchSubscriptionTxns;
	frm.page.value = p;
	frm.submit();
}
function ChangeSubscription(elm,mporder_id){
	var agree = confirm($(elm).attr('data'));
	if(!agree){
		return false;
	}
	callAjax( generateUrl('account','confirm_change_subscription'), 'mporder_id='+mporder_id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			$.facebox('Oops! Internal error.');
			return;
		}
		if (ans.status == 0){
			$.facebox(ans.msg);
			return;
		}
		if(ans.status == 1){
			$.facebox( ans.msg );
			setTimeout( function(){
				window.location = ans.redirectUrl;
			}, 1000 );
		}
		
	});
}


function closePreDeactivateAutoRenewalBox(){
	jQuery(document).trigger('close.facebox');
}
