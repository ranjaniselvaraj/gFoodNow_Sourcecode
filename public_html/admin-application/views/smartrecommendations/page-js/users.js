function searchUserRecommendedProducts(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#userrecommendedproducts-list')); 
	callAjax(generateUrl('smartrecommendations', 'listUserRecommendedProducts'), data, function(t){ 
		$('#userrecommendedproducts-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchUserRecommendedProducts(frm);
}
  
function clearSearch() {
	document.frmSearchUserProducts.reset();
	$("#frmSearchUserProducts input[type=hidden]").val("");
	searchUserRecommendedProducts(document.frmSearchUserProducts);
}
$(document).ready(function(){
	searchUserRecommendedProducts(document.frmSearchUserProducts);
});
$(function() {
	
	$('input[name=\'user_name\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'users_autocomplete',[],webroot),
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
				$('input[name=\'user_name\']').val(suggestion.value);
				$('input[name=\'user\']').val(suggestion.data);
        	 	
    	 }
	});
	
	
	$('input[name=\'user_name\']').keyup(function(){
		$('input[name=\'user\']').val('');
	})
})
	 