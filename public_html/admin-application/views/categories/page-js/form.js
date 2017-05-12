$(function() {
	
	$('input[name=\'path\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'categories_autocomplete',[1],webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) {
						json.suggestions.unshift({
							data: 0,
							value: '--- None ---'
						});
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'path\']').val(suggestion.value);
				$('input[name=\'category_parent\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'filter\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'filtergroupoptions_autocomplete',[],webroot),
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
				$('input[name=\'filter\']').val('');
				$('#category-filter' + suggestion.data).remove();
				$('#category-filter').append('<div id="category-filter' + suggestion.data + '"><i class="remove_filter remove_param"><img src="'+webroot+'images/admin/closelabels.png"/></i> ' + suggestion.value + '<input type="hidden" name="category_filter[]" value="' + suggestion.data + '" /></div>');
    	 }
	});
	
$('#category-filter').delegate('.remove_filter', 'click', function() {
	$(this).parent().remove();
});
})
