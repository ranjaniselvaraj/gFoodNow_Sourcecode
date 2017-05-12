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
	alert(htmlDecode(editableObj.innerHTML));
	$.ajax({
		url: generateUrl("reviews", "update_review"),
		type: "POST",
		data:'&value='+htmlDecode(editableObj.innerHTML)+'&id='+id,
		success: function(data){
			clicked = 0;
			$(editableObj).html(data);
			$(editableObj).css("background","#FDFDFD");
		}        
   });
}

