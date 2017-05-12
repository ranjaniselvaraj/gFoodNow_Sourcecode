var track_loaded_pages=0;
var processing_product_load = false;
$(document).ready(function(){
	filterProducts(1);	
})
function filterProducts(page){
	if(processing_product_load == true) return false;
	processing_product_load = true;
	showCssElementLoading($('#products-list'), 1);
	callAjax(generateUrl('account', 'ajax_load_favorite_products_json'), 'pagesize=30&page='+page, function(response){
			loadproducts(response,1);
	});
}
function loadproducts(response,append){
		var ans = parseJsonData(response);
		$('#total_records').html(ans.count);
		var tmpl = ans.html;
		var html = Mustache.render(tmpl, ans);
		track_loaded_pages++;
		if(ans === false){
			processing_product_load = false;
			return false;
		}
		if(append == 1){
			$('#loader').hide();
			$('#products-list').append(html);
		}else{
			$('#products-list').html(html);
		}
		total_pages=ans.total_pages;
		$(window).scroll(function() { 
			//if($(window).scrollTop() + $(window).height() >= $(document).height())  {
			if(($(window).scrollTop() + $(window).height() >= $(document).height() - $(".footer").height()))  {	
				if ((processing_product_load==false) && (track_loaded_pages < total_pages )) {
					filterProducts(track_loaded_pages+1);
					return;
				}
			}
		});
		processing_product_load = false;
		//return false;
}
