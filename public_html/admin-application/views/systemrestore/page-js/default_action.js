// JavaScript Document
$(function() {
	$('#myonoffswitch').change(function() {
		var isChecked=0
	   	if($(this).is(":checked")) {
    		 isChecked=1
   		}
	   	callAjax(generateUrl('systemrestore', 'update_setting',[isChecked] ), '&outmode=json', function(t){	 
			alert(t);
		});
	});
		
})