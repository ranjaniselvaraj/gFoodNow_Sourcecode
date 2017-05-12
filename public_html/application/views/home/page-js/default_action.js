$(document).ready(function(){
		$('.new-slider').slick({
		  dots: false,
		  infinite: true,
		  speed: 1000,
		  fade: true,
		  arrows:false,
		  cssEase: 'linear',
		  autoplay: true
		});
	
	 $('.prev-btn').click(function(){
          $('.new-slider').slick('slickPrev');
        });
        
      $('.next-btn').click(function(){
          $('.new-slider').slick('slickNext');
      }); 
	
	
	}); 
 $(document).ready(function() {   
 
 	// Slideshow 1
      /*$("#slider1").responsiveSlides({
        auto: true,
		maxwidth: 1205,
        pager: false,
        nav: true,
        speed: 1000,
        namespace: "callbacks",
        before: function () {
          $('.events').append("<li>before event fired.</li>");
        },
        after: function () {
          $('.events').append("<li>after event fired.</li>");
        }
      });*/
	    
    $(".products-carousel-belt").owlCarousel({
	    autoPlay: 3000, //Set AutoPlay to 3 seconds
		pagination: true,
		navigation: false,
		     
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
	
	
    $(document).ready(function () {
        $('.main-category').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion           
            width: 'auto', //auto or any width like 600px
            fit: true,   // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#tabInfo');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });        
    });
	
	
    $(document).ready(function () {
        $('#slide-animation').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion           
            width: 'auto', //auto or any width like 600px
            fit: true,   // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#tabInfo');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
    });
	
    	
	$(document).ready(function() { 
		setTimeout(function(){ $(".load_later").each(function() {
			$(this).attr('src',$(this).attr('data-img-src'));
			}); 
		}, 3000);
	})
	
	
		