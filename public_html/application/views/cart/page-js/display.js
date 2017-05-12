$(document).ready(function() {     
	$(".refresh").on('click', function(event){
			event.preventDefault();
			$("#cartFrm").submit();
	});
	
	setTimeout(function(){ $(".product_load_later").each(function() {
			$(this).attr('src',$(this).attr('data-img-src'));
			}); 
		}, 2000);	
		
	$(".products-carousel").owlCarousel({
	    autoPlay: 3000, //Set AutoPlay to 3 seconds
		pagination: true,
		navigation: true,
		     
    	itemsCustom : [
			[0, 1],
			[450, 1],
			[600, 2],
			[700, 2],
			[1000, 3],
			[1200, 4],
			[1400, 4],
			[1600, 4]
		],
    });
	
});
