$(document).ready(function() {
	
	
	 
	
	/* for navigation drop down */    
		$('.navchild').hover(function() {
           var el = $("body");
           if($(window).width()>767){
$(this).addClass("active");
el.addClass("nav_show");
           }    
           return false; 
       } , function() {
           var el = $("body");
           if($(window).width()>767){
$(this).removeClass("active");
el.removeClass("nav_show");
           }    
           return false; 
       });
		
		
		/* for mobile navigations */	
          $('.link__mobilenav').click(function(){

              if($(this).hasClass('active')){
                  $(this).removeClass('active');
                  $(this).siblings('.navigations > li .subnav').slideUp();
                  return false;
              }
              $('.link__mobilenav').removeClass('active');
              $(this).addClass("active");
              if($(window).width()<767){
                  $('.navigations > li .subnav').slideUp();
                  $(this).siblings('.navigations > li .subnav').slideDown();
              }
              return;
          });
		  
		  
		   /* for mobile toggle navigation */    
		$('.navs_toggle').click(function() {
            $(this).toggleClass("active");
			var el = $("body");
			if(el.hasClass('toggled_left')) el.removeClass("toggled_left");
			else el.addClass('toggled_left');
            return false; 
        });
		
		$('body').click(function(){
            if($('body').hasClass('toggled_left')){
                $('.navs_toggle').removeClass("active");
                $('body').removeClass('toggled_left');
            }
        });
    
        $('.mobile__overlay').click(function(){
            if($('body').hasClass('toggled_left')){
                $('.navs_toggle').removeClass("active");
                $('body').removeClass('toggled_left');
            }
        });
		
		
		$('.navpanel,.section_primary').click(function(e){
            e.stopPropagation();
            //return false;
        });
		
	
	

 


	
	

 /* for footer */
        if($(window).width()<767){
          $('.gridspanel_title').click(function(){

              if($(this).hasClass('active')){
                  $(this).removeClass('active');
                  $(this).siblings('.gridspanel_content').slideUp();
                  return false;
              }
              $('.gridspanel_title').removeClass('active');
              $(this).addClass("active");
              
                  $('.gridspanel_content').slideUp();
                  $(this).siblings('.gridspanel_content').slideDown();
            
              return;
          });
          }    
		  
		  


 
      
});





           


 