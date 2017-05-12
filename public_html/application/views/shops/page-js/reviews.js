var processing_reviews_load = false;
$(document).ready(function() {
	$('body,html').animate({
		scrollTop: $(".description").offset().top-100
    }, 800);
			
	searchReviews(document.frmSearch);
});
function listNextPage(){
	var frm = document.frmSearch;
	frm.page.value = parseInt(frm.page.value) + 1;
	searchReviews(frm,1);
}
var track_loaded_pages=0;
function searchReviews(frm, append){
	if(processing_reviews_load == true) return false;
	processing_reviews_load = true;
	var data = getFrmData(frm);
	$('#loader').remove();
	showCssElementLoading($('#reviews-list'), append);
	callAjax(generateUrl('common', 'ajax_show_reviews'), data, function(t){ 
		track_loaded_pages++;
		var ans = parseJsonData(t);
		if(ans === false){
			processing_reviews_load = false;
			return false;
		}
		if(append == 1){
			$('#loader').remove();
			$('#reviews-list').append(ans.html);
		}else{
			$('#reviews-list').html(ans.html);
		}
		total_pages=ans.total_pages;
		$(window).scroll(function() { 
			//if($(window).scrollTop() + $(window).height() >= $(document).height())  {
			if(($(window).scrollTop() + $(window).height() >= $(document).height() - $(".footer").height()))  {	
				if ((processing_reviews_load==false) && (track_loaded_pages < total_pages )) {
					listNextPage();
					return;
				}
			}
		});
		
		processing_reviews_load = false;
		return false;
	});
	return false;
}
$(document).ready(function() {
    // Configure/customize these variables.
    var showChar = 500;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show more";
    var lesstext = "Show less";
    
    $('.more').each(function() {
        var content = $(this).html();
 
        if(content.length > showChar) {
 
            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);
 
            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';
 
            $(this).html(html);
        }
 
    });
 
    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
});
