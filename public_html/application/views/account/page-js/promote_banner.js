$(document).ready(function() {     
	$('.time').datetimepicker({
			datepicker: false,
			format:'H:i',
			step: 30
	});
	
	$( "#promotion_budget_period,#promotion_start_date" ).change(function() {
		var new_date = AddDaysToDate($("#promotion_start_date").val(),$("#promotion_budget_period").val(),"-")
		$("#promotion_end_date").val(new_date);
	});
	
	
	$('#fancy_popup_box').magnificPopup({
          type: 'image'
        });
		
	/*$('#fancy_popup_box').magnificPopup({
          type: 'inline',
          preloader: false,
          focus: '#name',
       });*/
	
	/*$( "#promotion_banner_position" ).change(function() {
		if($(this).val()){
			$("#banner_position").html("<div class='banner_position_div'><img src="+webroot+"images/banner-positions/"+$(this).val()+".jpg ></div>");
		}else{
			$("#banner_position").html('');
		}
		//alert($(this).val());avatar
	});*/
	
});
