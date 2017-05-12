$(function(){
	$(".ajax_sitemap" ).each(function( index ) {
	href= $(this).attr('data-href');
	id= $(this).attr('id');
	el = $('#'+id);
	showHtmlElementLoadingSmall(el);
		if(href!=undefined && href!='') {
			$.ajax({
			url : href,
			type: "POST",
			async : false,
			success: function(response, textStatus, jqXHR){
				 $('#'+id).html(response);
				}
			});
		}
	});
	
	$(document).on( "click", "ul.brands > li > a", function() {
		elem=$(this).parent().parent();
		$(elem).find('li > a').removeClass("linkselect");
		$(this).addClass('linkselect'); 
		$(".jscroll-added").remove();
		var pane = $('#'+$(this).attr('lang'));
		showHtmlElementLoadingSmall(pane);
		href=$(this).attr('href')
		pane.load(href, function() {
		pane.data('jscroll', null);
		pane.jscroll({
				autoTrigger: false,
			  });
		  });
		return false;
	});
	
	$(document).on( "click", "ul.stores > li > a", function() {
		elem=$(this).parent().parent();
		$(elem).find('li > a').removeClass("linkselect");
		$(this).addClass('linkselect'); 
		$(".jscroll-added").remove();
		var pane = $('#'+$(this).attr('lang'));
		showHtmlElementLoadingSmall(pane);
		href=$(this).attr('href')
		pane.load(href, function() {
		pane.data('jscroll', null);
		pane.jscroll({
				autoTrigger: false,
			  });
		  });
		return false;
	});
	
	
});
