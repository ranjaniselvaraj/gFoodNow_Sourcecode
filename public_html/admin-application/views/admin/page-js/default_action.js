$(document).ready(function() {
	 // tabbed content
    $(".tabs_content").hide();
    $(".tabs_content:first").show();
	  /* if in tab mode */
    	$(".detailTabs li a").click(function(event) {
		      $(".tabs_content").hide();
	      var activeTab = $(this).attr("rel"); 
    	  $("#"+activeTab).fadeIn();		
	      $(".detailTabs li").removeClass("active");
    	  $(this).parent().addClass("active");
    });
	
	$(".summarytabs_content").hide();
    $(".summarytabs_content:first").show();
	  /* if in tab mode */
    	$(".summarydetailTabs li a").click(function(event) {
		      $(".summarytabs_content").hide();
	      var activeTab = $(this).attr("rel"); 
    	  $("#"+activeTab).fadeIn();		
	      $(".summarydetailTabs li").removeClass("active");
    	  $(this).parent().addClass("active");
    });
	
});
