$(document).ready(function(){
	$('#frmSettings input').on('change', function() {
		var et = $('input[name=export_type]:checked', '#frmSettings').val();
		if(et=='c' || et=='p'){
			$('.showHide').closest('tr').show();
		}else{
			$('.showHide').closest('tr').hide();
		}		
	});
	
	/* $('#max').on('blur',function(){		
		var rt= $('input[name=range_type]:checked', '#frmSettings').val();
		if(rt=='page'){				
			var max=$('#max').val();
			if(max<=1){$('#max').val(2);}
		}
	}); */
	$('#max').on('blur',function(){
		var max=$(this).val();
		if(isNaN(max)){$(this).val('');}		
		if(max<=1){$(this).val(2);}
			
	});
	
	$('#min').on('blur',function(){		
		var min=$(this).val();
		if(isNaN(min)){$(this).val('');}	
		if(parseInt(min)<=1){$(this).val(1);}		
	});
});
function loadRangeType(obj)
{	
	if(obj.value=='page'){
		$('#minLabel').html('Counts per batch');
		$('#maxLabel').html('The batch number');
	}else{
		$('#minLabel').html('Start id');
		$('#maxLabel').html('End id');
	}
}
function isNumber(txt){ 
	var regExp=/^[\d]{1,}$/;
	return regExp.test(txt); 
}