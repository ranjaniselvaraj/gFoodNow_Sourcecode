$(document).ready(function(){
		$("#status").bind("change", function() {
				var response = parseJsonData(response);
				if (this.value==2){
					$("#div_comments_box").show();
					supplierRequestFormValidator_requirements['comments']={"required":true};
				}else{
					$("#div_comments_box").hide();
					supplierRequestFormValidator_requirements['comments']={"required":false};
				}
				supplierRequestFormValidator.resetFields();	
			});
		$("#status").trigger("change");
	})
