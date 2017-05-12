function preload(arrayOfImages) {
    $(arrayOfImages).each(function () {
        $('<img />').attr('src',this).appendTo('body').css('display','none');
    });
}
$(document).ready(function() {     
	
	
	$(document).on('click', '.logged_in', function(event){
			var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(logged) {
					if (logged==true){
						return
					}else{
						event.preventDefault();
						login_popupbox();
					}
		});
	});
	
		
	$(document).ready(function () {
	
		jQuery.jQueryTab({
			responsive:true,							// enable accordian on smaller screens
			collapsible:true,							// allow all accordions to collapse 
			useCookie: true,							// remember last active tab using cookie
			openOnhover: false,						// open tab on hover
			initialTab: 1,								// tab to open initially; start count at 1 not 0
			cookieName: 'active-tab',			// name of the cookie set to remember last active tab
			cookieExpires: 4,							// when it expires in days or standard UTC time
			cookiePath: '/',							// path on which cookie is accessible
			cookieDomain:'',							// domain of the cookie
			cookieSecure: false,					// enable secure cookie - requires https connection to transfer
			tabClass:'tabs',							// class of the tabs
			headerClass:'accordion_tabs',	// class of the header of accordion on smaller screens
			contentClass:'tab_content',		// class of container
			activeClass:'active',					// name of the class used for active tab
			tabTransition: 'fade',				// transitions to use - normal or fade
			tabIntime:500,								// time for animation IN (1000 = 1s)
			tabOuttime:0,									// time for animation OUT (1000 = 1s)
			accordionTransition: 'slide',	// transitions to use - normal or slide
			accordionIntime:500,					// time for animation IN (1000 = 1s)
			accordionOuttime:400,					// time for animation OUT (1000 = 1s)
			before: function(){},					// function to call before tab is opened
			after: function(){}						// function to call after tab is opened
		});
});
	
	setTimeout(function(){ $(".product_load_later").each(function() {
			$(this).attr('src',$(this).attr('data-img-src'));
			}); 
		}, 2000);	
		
	$('#product-gallery').eagleGallery({
		openGalleryStyle: 'transform',
		changeMediumStyle: true,
		rewindNav: true,
	});
	
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
			[1200, 6],
			[1400, 6],
			[1600, 6]
		],
    });
				
	$(".recommended-carousel").owlCarousel({     
		autoPlay: 3000, //Set AutoPlay to 3 seconds
		pagination: false,
		navigation: true,     
		items : 6,
		itemsDesktop : [1199,3],
		itemsDesktopSmall : [979,3]
	});
		
	
	$('.date').datetimepicker({
			timepicker: false,
			format:'Y-m-d',
			formatDate:'Y-m-d',
			step: 10
	});
		
	$('.time').datetimepicker({
			datepicker: false,
			format:'H:i',
			step: 10
	});
		
	$('.datetime').datetimepicker({
			datepicker: true,
			timepicker: true,
			format:'Y-m-d H:i',
			step:10
	});
	
	$('[id^=\'button-upload\']').on('click', function() {
			var node = this;
			$('#form-upload').remove();
			//$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
			$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" ><input type="file" name="file" /></form>');
			$('#form-upload input[name=\'file\']').trigger('click');
			if (typeof timer != 'undefined') {
				clearInterval(timer);
			}
			timer = setInterval(function() {
				if ($('#form-upload input[name=\'file\']').val() != '') {
					clearInterval(timer);
					$val = $(node).val();
					$.ajax({
						url: generateUrl('common', 'file_upload'),
						type: 'post',
						dataType: 'json',
						data: new FormData($('#form-upload')[0]),
						cache: false,
						contentType: false,
						processData: false,
						beforeSend: function() {
							$(node).val('Loading');
						},
						complete: function() {
							$(node).val($val);
						},
						success: function(json) {
								$('.text-danger').remove();
								if (json['error']) {
									$(node).parent().find('input[type=button]').after('<div class="text-danger">' + json['error'] + '</div>');
								}
								if (json['success']) {
									//alert(json['success']);
									//alert(json['success']);
									$(node).parent().find('input[type=button]').after('<div class="text-success">' + json['success'] + '</div>');
									$(node).parent().find('input').attr('value', json['code']);
									
								}
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}
				}, 500);
	})
	
	$("#cart-button").on('click', function(event){
			event.preventDefault();
			HideJsSystemMessage();
			var data = $("#frmBuyProuct").serialize();
			var yourArray = [];
    			$(".cart-tbl").find("input").each(function(){
		        if (($(this).val()>0) && (!$(this).parent().parent().parent().hasClass("cancelled"))){
					 data = data+'&'+$(this).attr('lang')+"="+$(this).val();	 
	    	    }
    		});
			data = data+'&pdetail=1';
			callAjax(generateUrl('cart', 'add'), data, function(response){
				//alert(JSON.stringify(response));
				//alert(response);
				$('.alert, .text-danger').remove();
				$('.form-group').removeClass('has-error');
				$('.cart-tbl').removeClass('has-error');
				
				var response = parseJsonData(response);
				if (response['error']) {
					if (response['error']['option']) {
						for (i in response['error']['option']) {
							var element = $('#input-option' + i.replace('_', '-'));
							if (element.parent().hasClass('input-group')) {
								element.parent().after('<div class="text-danger">' + response['error']['option'][i] + '</div>');
							} else {
								element.after('<div class="text-danger">' + response['error']['option'][i] + '</div>');
							}
						}
					}
					
					if (response['error']['addon']) {
						for (i in response['error']['addon']) {
							ShowJsSystemMessage(response['error']['addon'][i]);
						}
					}
					
					if (response['error']['product']){
						ShowJsSystemMessage(response['error']['product']);
					}
					$('.text-danger').parent().addClass('has-error');
				}
				if (response['success']) {
					ShowJsSystemMessage(response['success']);
					$('.count_cart_items').html(response['total']);
					$("#cart_summary").trigger( "click" );
					$('#list_cart_summary').load(generateUrl('cart', 'cart_summary'));
					
				}
			})
	});
	
});
$(document).ready(function () {
		$('.accordion-toggle').on('click', function(event){
			event.preventDefault();
			// create accordion variables
			var accordion = $(this);
			var accordionContent = accordion.next('.accordion-content');
			var accordionToggleIcon = $(this).children('.toggle-icon');
			// toggle accordion link open class
			accordion.toggleClass("open");
			// toggle accordion content
			accordionContent.slideToggle(250);
			// change plus/minus icon
			if (accordion.hasClass("open")) {
				accordionToggleIcon.html("<i class='fa fa-minus-circle'></i>");
			} else {
			accordionToggleIcon.html("<i class='fa fa-plus-circle'></i>");
			}
		});
		
		$('table').on('click','tr a.cancel',function(e){
			e.preventDefault();
		  	$(this).closest('tr').toggleClass('cancelled');
		});
});
var processing_reviews_load = false;
$(document).ready(function() {
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
$(window).unload(function(){
	 var data = $("#frmBuyProuct").serialize();
	 callAjax(generateUrl('products', 'log_time'), data, function(t){
		 console.log(t);
		 //alert(t);
	  })
});
