 $(document).ready(function() {   
 
	$(".products-carousel").owlCarousel({
	    autoPlay: 3000, //Set AutoPlay to 3 seconds
		pagination: true,
		navigation: false,
    	itemsCustom : [
			[0, 1],
			[450, 1],
			[600, 2],
			[700, 2],
			[1000, 3],
			[1200, 6],
			[1400, 6],
			[1600, 6]
		],
    });
	
 });
	
	
	
    	
	$(document).ready(function() { 
		setTimeout(function(){ $(".load_later").each(function() {
			$(this).attr('src',$(this).attr('data-img-src'));
			}); 
		}, 3000);
	})
	
	
		