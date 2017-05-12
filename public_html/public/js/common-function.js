
if ( (location.hash == "#_=_" || location.href.slice(-1) == "#_=_") ) {
    removeHash();
}

var ww = document.body.clientWidth;

$(document).ready(function() {
	$(".nav li a").each(function() {
		if ($(this).siblings('span.span_parent').length < 1 && $(this).next().length > 0) {
			$(this).parent().prepend("<span class='span_parent'></span>");
		};
	});
	
	$(".buttonToggle").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("active");
		$(".navBar").toggle();
	});
	adjustMenu();
})

$(window).bind('resize orientationchange', function() {
	ww = document.body.clientWidth;
	$(document).ready(function(){ adjustMenu(); });
});

var adjustMenu = function() {
	$(".nav>li").addClass("hover");
	if (ww < 751) {
		$(".nav").show();
		$(".nav li").unbind('mouseenter mouseleave')
		$(".nav li span.span_parent").unbind('click').bind('click', function(e) {
			// must be attached to anchor element to prevent bubbling
			e.preventDefault();
			$(this).parent('li').siblings('.nav li').find('div.sub_menu').hide();
			$(this).parent("li").find('div.sub_menu').toggle();
			
		});
	}else if (ww >= 751) {
		$(".buttonToggle").css("display", "none");
		$(".navBar").show();
		//$(".nav li a").unbind('click');
		$(".nav>li").unbind('mouseenter mouseleave').bind('mouseenter', function(){
			var el_hover = $(this);
			$(this).find("div.sub_menu").fadeIn("slow", function(){
				if(!el_hover.is(':hover')){
					el_hover.find("div.sub_menu").hide();
				}
			});
			return false;
		}).bind('mouseleave', function(){
			$(this).find("div.sub_menu").hide(); 
			return false;
		});
		
	}
	return false;
}

$("body").on("click",function(e) {
		if( $(e.target).is('.nav') || $(e.target).closest('.nav').length) {
			
		}else {
			$(".nav").find("div.sub_menu").hide(); 
		}
});


/***/



$(window).scroll(function(){
	if ($(window).scrollTop() >= 1) {
       $('html').addClass('header-fixed');
    }
    else {
       $('html').removeClass('header-fixed');
    }
    /*if ($(window).scrollTop() >= 44) {
       $('.top-head').addClass('sticky');
    }
    else {
       $('.top-head').removeClass('sticky');
    }*/
});

/* scrollTop() >= 240
   Should be equal the the height of the header
 */
 
 $('.dash-nav li').click(function(e) {
   // e.preventDefault();  
    $('.dash-nav li').removeClass('active');
    $(this).addClass('active');
});


  	
	
	$('.click_trigger').click(function(e){
		e.stopPropagation();
		var trg_id = this.id;
		$('#list_' + trg_id).toggleClass('enable_div');
		return false;
	});
	
	$('#list_ct_5').click(function(ev){
		ev.stopPropagation();	
	});
	
	/*$('body a, body input, body button').click(function(ev){
		//ev.stopPropagation();	
	});*/
	
	$('.tab-no-action').click(function(ev){
		ev.stopPropagation();	
	});
	
				
	$('body').click(function(e){
		$('.enable_div').removeClass('enable_div');
	});






$(function(){
	var ink, d, x, y;
	$(".ripplelink").click(function(e){
    if($(this).find(".ink").length === 0){
        $(this).prepend("<span class='ink'></span>");
    }
         
    ink = $(this).find(".ink");
    ink.removeClass("animate");
     
    if(!ink.height() && !ink.width()){
        d = Math.max($(this).outerWidth(), $(this).outerHeight());
        ink.css({height: d, width: d});
    }
     
    x = e.pageX - $(this).offset().left - ink.width()/2;
    y = e.pageY - $(this).offset().top - ink.height()/2;
     
    ink.css({top: y+'px', left: x+'px'}).addClass("animate");
});
});


$(document).ready(function () {

    // hide #back-top first
    $("#back-top").hide();

    // fade in #back-top
    $(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('#back-top').fadeIn();
            } else {
                $('#back-top').fadeOut();
            }
        });

        // scroll body to 0px on click
        $('#back-top a').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
    });
	
	
	//equalHeight($(".equal_height_shop_item"));
	$( window ).resize(function() {
		//setTimeout(function(){ equalHeight($(".equal_height_shop_item")); }, 1000);
	});
	
	
	$('footer.footer h3').click(function(e) {
		$('footer.footer h3').not($(this)).removeClass('active');
		$(this).toggleClass('active');
		var $div = $(this).next('.f-cell ul');
		$(".f-cell ul").not($div).removeClass('open');
		$div.toggleClass('open');
	});

});
	
	function escapeRegExp(str) {
 	   return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
	}
	function replaceAll(str, find, replace) {
	  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
	}
	
	

	var theme_link_el_obj = null;
	var themeSwitcher = function(theme){
		callAjax(generateUrl('common', 'set_cookie'), 'theme='+theme, function(response){
			var theme_link_el = document.createElement('link');
			$(theme_link_el).attr({'rel':'stylesheet', 'type':'text/css', 'href':webroot+'public/css/theme-color.php'});
			window.theme_link_el_obj = $('head').append(theme_link_el).find('link:last');
		})
};

$(document).ready(function () {
	$('.front-color-theme-switcher li').click(function(){
		var color=$(this).attr('data-theme');
		$('.front-color-theme-switcher li').removeClass('active');
		$(this).addClass('active');
		themeSwitcher(color);
		return false;
	});
	
	$( "#frmSiteSearch,#frmProductsSearch" ).submit(function( event ) {
			var action = $(this).attr("action");
			var qryParam=($(this).serialize_without_blank());
			location = action+"?" + qryParam;
			return false;
	})
	
});

