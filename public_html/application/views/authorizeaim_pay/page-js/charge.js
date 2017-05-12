$(function(){	
	$("#cc_number" ).blur(function( ) {
		var obj=$(this);
		var cc=obj.val();		
		obj.removeClass();
		if(cc==''){obj.addClass('type-bg');}
		else{
			var data="cc="+cc;		
			callAjax(generateUrl('authorizeaim_pay', 'check_card_type'), data, function(t){
				var ans = parseJsonData(t);
				var card_type=ans.card_type.toLowerCase();				
				obj.addClass(card_type);			
			});
		}
	});
	
});