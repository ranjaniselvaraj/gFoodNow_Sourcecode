$(document).ready(function(){
	
	$('.signUp').bind('click' , function(){
		
		var package_id = Number($(this).data('package_id'));
		var sub_package_id = $("input[name='package_" + package_id + "']:checked").val();
		sub_package_id = Number(sub_package_id);
		callAjax( generateUrl('subscription_packages', 'signup_to_subscribe'), 'sub_package_id=' + sub_package_id, function(t) {
			var ans = parseJsonData(t);
		
			if (ans === false){
				$.facebox('Oops! Internal error.');
				ajax_processing = false;
				return;
			}
			
			if (ans.status == 0){
				ajax_processing = false;
				$.facebox(ans.msg);
				return;
			}
			
			if( ans.status == 1 ){
				$.facebox( ans.msg );
				setTimeout( function(){
					window.location = ans.redirectUrl;
				}, 1000 );
			}
			ajax_processing = false;
		});
		
	});
	
});