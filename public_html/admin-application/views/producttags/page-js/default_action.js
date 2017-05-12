function searchProductTags(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#product-tags-list')); 
	callAjax(generateUrl('producttags', 'listProductTags'), data, function(t){
		$('#product-tags-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchProductTags(frm);
}
$(document).ready(function(){
		searchProductTags(document.frmProductTagSearch);
});
  
function clearSearch() {
	document.frmProductTagSearch.reset();
	$("#frmProductTagSearch input[type=hidden]").val("");
	searchProductTags(document.frmProductTagSearch);
}

function ConfirmProductTagDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('producttags', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchProductTags(document.frmProductTagSearch);
		});
    });
    return false;
}
