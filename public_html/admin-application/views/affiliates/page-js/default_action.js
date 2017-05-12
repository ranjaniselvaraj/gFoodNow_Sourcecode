function searchAffiliates(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#affiliates-list')); 
	callAjax(generateUrl('affiliates', 'listAffiliates'), data, function(t){
		$('#affiliates-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchAffiliates(frm);
}
$(document).ready(function(){
		searchAffiliates(document.frmSearchAffiliates);
});
  
function clearSearch() {
	document.frmSearchAffiliates.reset();
	$("#frmSearchAffiliates input[type=hidden]").val("");
	searchAffiliates(document.frmSearchAffiliates);
}
function ApproveAffiliate(id, el) {
	callAjax(generateUrl('affiliates', 'approve'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true);
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		$(el).closest("li").html('<a class="toggleswitch deactivated"><i class="ion-thumbsup icon"></i></a>');
	});
}
function UpdateAffiliateStatus(id, el) {
	callAjax(generateUrl('affiliates', 'update_affiliate_status'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true);
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

function ConfirmAffiliateDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('affiliates', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchAffiliates(document.frmSearchAffiliates);
		});
    });
    return false;
}
