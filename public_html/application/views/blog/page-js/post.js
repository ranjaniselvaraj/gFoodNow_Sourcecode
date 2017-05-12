 function searchPost(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#comment-post-list'));
	callAjax(generateUrl('blog', 'listCommentsOfPost'), data, function(t){
		$('#comment-post-list').html(t);
	});
}
function listPages(p){
	var frm = document.frmPaging;
	frm.page.value = p;
	searchPost(frm);
}
$(document).ready(function(){
	showHtmlElementLoading($('#comment-post-list'));
	var post_id = $('#comment_post_id').val();
	var data = 'post_id='+post_id+'&page=1';
	callAjax(generateUrl('blog', 'listcommentsofpost'), data, function(t){
		$('#comment-post-list').html(t);
	});
	
$('.blog_slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
     autoplay: true,
        asNavFor: '.slider_nav'
        });
        $('.slider_nav').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        asNavFor: '.blog_slider',
        dots: false,
        centerMode: false,
             autoplay: true,
        focusOnSelect: true,
        fade: false,
       responsive: [
   /* {
      breakpoint: 1024,
      settings: {
        slidesToShow: 4,
        slidesToScroll: 1,
      }
    },*/
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2
      }
    }
    // You can unslick at a given breakpoint now by adding:
    // settings: "unslick"
    // instead of a settings object
  ]
});
	
});
