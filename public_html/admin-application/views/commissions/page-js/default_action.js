$(document).ready(function() {
	$(".delete" ).click(function( event ) {
		    var me = $(this);
			event.preventDefault();
			if ( me.data('requestRunning') ) {
     		   return;
		    }
			if(confirm("Sure you want to remove this item ?")){
			me.data('requestRunning', true);
			var id = $(this).attr("id");
			var data = "id="+id;
			data += '&outmode=json&is_ajax_request=yes';
			var href=generateUrl('commissions', 'remove',[]);
	       	callAjax(href, data, function(response){
			    me.data('requestRunning', false);
			    var ans = parseJsonData(response);
				if (ans.status==1){
					$("#comm-row"+id).remove();
				}else{
					me.parent().html(ans.msg);
				}
			})
		}
	});
	
})
