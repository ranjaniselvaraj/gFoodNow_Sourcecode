$(function(){
	$(".ajax_sitemap" ).each(function( index ) {
	href= $(this).attr('data-href');
	id= $(this).attr('id');
	el = $('#'+id);
	showCssElementLoading(el);
		if(href!=undefined && href!='') {
			$('#'+id).load(href);
		}
	});
	
	$(document).on( "click", "ul.brands > li > a", function() {
		elem=$(this).parent().parent();
		$(elem).find('li > a').removeClass("linkselect");
		$(this).addClass('linkselect'); 
		$(".jscroll-added").remove();
		var pane = $('#'+$(this).attr('lang'));
		showCssElementLoading(pane);
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
