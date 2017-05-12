var clicked = 0;
function showEdit(editableObj) {
	clicked = clicked+1;
	if (clicked==1){
		$(editableObj).html(htmlEncode(editableObj.innerHTML));
	}
	$(editableObj).css("background","#FFF");
} 

function saveToDatabase(editableObj,id) {
	$(editableObj).css("background","#FFF url("+webroot+"images/admin/loaderIcon.gif) no-repeat right");
	console.log(clicked);
	$.ajax({
		url: generateUrl("messages", "update_message"),
		type: "POST",
		data:'&value='+htmlDecode(editableObj.innerHTML)+'&id='+id,
		success: function(data){
			clicked = 0;
			$(editableObj).html(data);
			$(editableObj).css("background","#FDFDFD");
		}        
   });
}

function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('messages', 'delete'), 'id=' + id, function(t){
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
