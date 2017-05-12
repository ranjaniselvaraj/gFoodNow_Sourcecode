$(document).ready(function(){
		$('#test_smtp_mail').click(function(e){ 
				e.preventDefault();
				$('#result_smtp_mail').append('<img src="'+webroot+'images/ajaxloadr.gif" style="margin-left 5px;" id="loader" alt="" />');
				var data=$("#frmConfigurations").serialize();
				var href=generateUrl('custom', 'test_smtp_email');
				callAjax(href, data, function(response){
					$("#result_smtp_mail").html(response);
				})
				
	})
	
	$('#test_mail').click(function(e){ 
				e.preventDefault();
				$('#result_mail').append('<img src="'+webroot+'images/ajaxloadr.gif" style="margin-left 5px;" id="loader" alt="" />');
				var data=$("#frmConfigurations").serialize();
				var href=generateUrl('custom', 'test_email');
				callAjax(href, data, function(response){
					$("#result_mail").html(response);
				})
				
	})
	
	
})
