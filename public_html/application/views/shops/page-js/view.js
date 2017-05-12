//JSON.stringify(dat)
var processing_product_load = false;
$(document).ready(function(){
		$('.buttonInfo').click(function() {
			$(this).toggleClass("active");
                $('.blueBar .grid_2').slideToggle("600");
        });
		$('.infolink').click(function() {
			$(this).toggleClass("active");
                $('.sectionDrop').slideToggle("600");return false;
        });
		$('html').click(function(){
			$('.sectionDrop').slideUp('slow');
			if($('.infolink').hasClass('active')){
				$('.infolink').removeClass('active');
			}
		});
	    $('.qLinksTab').click(function() {
			$(this).toggleClass("active");
                $('.iconLinks').slideToggle("600");
        });
		
		
		$('ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');
			$('ul.tabs li').removeClass('current');
			$('.filter-list').removeClass('current');
			$(this).addClass('current');
			$("#"+tab_id).addClass('current');
		})
		
});
$(document).ready(function(){
	
		
	$('body').on('change','.brands,.out_of_stock,.free_shipping,.condition,.filter_range,.price_range',function(){
        resetFormPagingandSearch();
    });
	$('body').on('click','.clear_all',function(event){
        event.preventDefault();
		$(this).parent().parent().find('.labelList li input:checkbox:checked').removeAttr('checked');
		resetFormPagingandSearch();
    });
	
	$('body').on('click','.sort',function(event){
        event.preventDefault();
		$(".sort").removeClass("selected_link");
		$(this).addClass("selected_link");
		var srt = $(this).attr('id');
		document.primarySearchForm.sort.value = srt;
		resetFormPagingandSearch();
    });
	
	$('body').on('click','.clear_price',function(event){
        event.preventDefault();
		$("input.price_range[data-index=0]").val('');
		$("input.price_range[data-index=1]").val('');
		resetFormPagingandSearch();
	});
    $("input.input-filter").change(function() {
        var $this = $(this);
		$("input.price_range[data-index=" + $this.data("index") + "]").val($this.val());
		resetFormPagingandSearch();
    });
	if ($("#products-list" ).length){
		filterProducts(document.primarySearchForm);
	}
});
function listNextPage(){
	var frm = document.primarySearchForm;
	frm.page.value = parseInt(frm.page.value) + 1;
	filterProducts(frm, 1);
}
function resetFormPagingandSearch(){
	track_loaded_pages=0;
	$(".left-panel").LoadingOverlay("show",{'image':''});
	var frm = document.primarySearchForm;
	frm.page.value = 1;
	var serializedData = $("#search-filters").serialize();
	
	var filters = cleanEmptyValues(serializedData);
	if (frm.sort.value!=""){
		filters = filters+"&sort="+frm.sort.value;
	}
	//alert(filters);
	push_string = "?"+filters;
	if (location.search.indexOf('?')>=0){
		push_string = "?tags="+getURLVar('tags')+"&"+filters;
	}
	window.history.pushState(null, null, push_string);
	filterProducts(document.primarySearchForm);
}
var track_loaded_pages=0;
function filterProducts(frm, append){
	//alert(frm);
	if(processing_product_load == true) return false;
	processing_product_load = true;
	$(".right-panel").LoadingOverlay("show",{'image':''});
	var data = getFrmData(frm);
	var serializedData = $("#search-filters").serialize();
	var filters = cleanEmptyValues(serializedData);
	data = data+'&'+filters;
	//alert(data);
	callAjax(generateUrl('common', 'ajax_show_products_json'), data, function(response){
		loadproducts(response,append);
	});
	return false;
}
function loadproducts(response,append){
		//$('.body').html(response);
		$(".left-panel,.right-panel").LoadingOverlay("hide",true);
		//alert(response);
		var ans = parseJsonData(response);
		$('#total_records').html(ans.count);
		//alert(JSON.stringify(response));
		
			var products_html = Mustache.render(ans.html, ans);
			$('#price_range_box,.resp_price_range_filter').hide();
			if (ans.count>0) {
				$('#price_range_box,.resp_price_range_filter').show();
			}
			if (ans.page==1){
				var brands_html='';
				if (ans.display_brands_box)
				var brands_html = Mustache.render(ans.brands_box_html, ans);
				var filtergroups_box_html = Mustache.render(ans.filtergroups_box_html, ans);
				$('#brands_box').html(brands_html);
				var min_val = $('#price_range_lower').val()>0?$('#price_range_lower').val():(ans.price_ranges.min_price);
				var max_val = $('#price_range_upper').val()>0?$('#price_range_upper').val():(ans.price_ranges.max_price);
				$("input.input-filter[data-index=0]").val(min_val);
				$("input.input-filter[data-index=1]").val(max_val);
				$('#filtergroups_box_html').html(filtergroups_box_html);
				$(".price_range_slider").slider({
					min: parseFloat(ans.price_ranges.min_price),
					max: parseFloat(ans.price_ranges.max_price),
					step: 1,
					values: [min_val, max_val],
					slide: function(event, ui) {
						if ( ( ui.values[ 0 ] + 19 ) >= ui.values[ 1 ] ) {
        	    			return false;
    	    			}
						for (var i = 0; i < ui.values.length; ++i) {
							$("input.input-filter[data-index=" + i + "]").val(ui.values[i]);
							$("input.price_range[data-index=" + i + "]").val(ui.values[i]);
						}
					},
					stop: function( event, ui ) { $('.enable_div').removeClass('enable_div'); resetFormPagingandSearch();}
				});
			}
		if (ans.count>0){	
			track_loaded_pages++;
			if(ans === false){
				processing_product_load = false;
				return false;
			}
			if(append == 1){
				$('#products-list').append(products_html);
			}else{
				$('#products-list').html(products_html);
			}
			//setTimeout(function(){ sticky_relocate(); }, 500); 
			total_pages=ans.total_pages;
			$(window).scroll(function() { 
				  if(($(window).scrollTop() + $(window).height() >= $(document).height() - $(".footer").height()))  {	
					if ((processing_product_load==false) && (track_loaded_pages < total_pages )) {
						listNextPage();
						return;
					}
				}
			});
			processing_product_load = false;
		}else{
			$('#products-list').html(ans.empty_box_html);
			processing_product_load = false;
		}
		
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
