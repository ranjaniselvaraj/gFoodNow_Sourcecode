$(document).ready(function(){
		ShowNavigationPages(document.frmNavigationSearch.navigation_id.value);
});
function ShowNavigationPages(id){
	callAjax(generateUrl('navigations', 'pages'), 'id=' + id, function(t){ 
		$('#navigations-list').html(t);
	});
}

function ConfirmNavigationPageDelete(id, nav) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('navigations', 'delete_page'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			ShowNavigationPages(nav);
		});
    });
    return false;
}
