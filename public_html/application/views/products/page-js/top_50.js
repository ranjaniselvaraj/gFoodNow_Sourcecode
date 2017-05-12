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
	filterProducts(document.primarySearchForm);
});
function filterProducts(frm, append){
	$("#products-list").LoadingOverlay("show",{'image':''});
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
function loadproducts(response){
		$("#products-list").LoadingOverlay("hide",true);
		var ans = parseJsonData(response);
		$('#total_records').html(ans.count);
			var products_html = Mustache.render(ans.html, ans);
			if (ans.count>0){	
			if(ans === false){
				return false;
			}
			$('#products-list').html(products_html);
		}else{
			$('#products-list').html(ans.empty_box_html);
		}
		//setTimeout(function(){ sticky_relocate(); }, 500); 
		
}
