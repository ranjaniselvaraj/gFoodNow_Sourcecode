$(document).ready(function() {
	$("#checkAll").change(function () {
    	$("input:checkbox").prop('checked', $(this).prop("checked"));
	});
});	