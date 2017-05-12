function searchTestimonials(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#testimonials-list')); 
	callAjax(generateUrl('testimonials', 'listTestimonials'), data, function(t){
		$('#testimonials-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchTestimonials(frm);
}
$(document).ready(function(){
		searchTestimonials(document.frmSearchTestimonials);
});
  
function clearSearch() {
	document.frmSearchTestimonials.reset();
	$("#frmSearchTestimonials input[type=hidden]").val("");
	searchTestimonials(document.frmSearchTestimonials);
}
function ConfirmDelete(id, el) {
	confirmBox("Are you sure you want to delete", function () {
		callAjax(generateUrl('testimonials', 'delete'), 'id=' + id, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			searchTestimonials(document.frmSearchTestimonials);
		});
    });
    return false;
}
