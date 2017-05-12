$(function() {

$('input[name=\'category\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
				url: generateUrl('categories', 'autocomplete',[1]),
				data: {keyword: encodeURIComponent(request) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item['name'],
							value: item['category_id']
						}
					}));
				}
			});
	},
	'select': function(item) {
		$('input[name=\'category\']').val('');
		$('#coupon-category' + item['value']).remove();
		$('#coupon-categories').append('<div id="coupon-category' + item['value'] + '"><i class="remove_filter remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' + item['label'] + '<input type="hidden" name="categories[]" value="' + item['value'] + '" /></div>');
	}
});

$('input[name=\'products\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'products_autocomplete',[],webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				 $('input[name=\'products\']').val('');
				 $('#coupon-products' + suggestion.data).remove();
				 $('#coupon-products').append('<div id="coupon-products' + suggestion.data + '"><i class="remove_product remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' + suggestion.value + '<input type="hidden" name="products[]" value="' + suggestion.data + '" /></div>');
		 }
	});
	
$('#coupon-categories').delegate('.remove_filter', 'click', function() {
	$(this).parent().remove();
});

$('#coupon-products').delegate('.remove_product', 'click', function() {
	$(this).parent().remove();
});



})
