function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('themes', 'delete'), 'id=' + id, function(t){
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
function ActivateTheme(id, el) {
	confirmBox("Are you sure you want to activate this theme?", function () {
		callAjax(generateUrl('themes', 'activate'), 'id=' + id, function(t){ 
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
		});
    });
    return false;
	
}
