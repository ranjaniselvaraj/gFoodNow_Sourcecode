function searchUsers(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#users-list')); 
	callAjax(generateUrl('users', 'listUsers'), data, function(t){
		$('#users-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchUsers(frm);
}
$(document).ready(function(){
		searchUsers(document.frmUserSearch);
});
  
function clearSearch() {
	document.frmUserSearch.reset();
	searchUsers(document.frmUserSearch);
}
function UpdateUserStatus(id, el) {
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

function ConfirmUserDelete(id, el) {
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
			searchUsers(document.frmUserSearch);
		});
    });
    return false;
	
}
