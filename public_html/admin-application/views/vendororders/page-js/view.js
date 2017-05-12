$(document).ready(function(){
		$("#opr_status").bind("change", function() {
			var href=generateUrl('common', 'check_is_shipping_mode',[],webroot);
			var data = 'val='+this.value;
	        callAjax(href, data, function(response){
				var response = parseJsonData(response);
				if (response["shipping"]){
					$("#div_tracking_number").show();
					OrderfrmValidator_requirements['tracking_number']={"required":true};
				}else{
					$("#div_tracking_number").hide();
					OrderfrmValidator_requirements['tracking_number']={"required":false};
				}
				OrderfrmValidator.resetFields();	
			})
									
		});
		$("#opr_status").trigger("change");
	})
