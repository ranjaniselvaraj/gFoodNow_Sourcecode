$(function() {
	
	$('input[name=\'product_name\']').devbridgeAutocomplete({
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
				$('input[name=\'product_name\']').val(suggestion.value);
				$('input[name=\'product_id\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'product_name\']').keyup(function(){
		$('input[name=\'product_id\']').val('');
	})
	
	
	$('input[name=\'shop_name\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('shops', 'autocomplete'),
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
				$('input[name=\'shop_name\']').val(suggestion.value);
				$('input[name=\'shop_id\']').val(suggestion.data);
        	 	
    	 }
	});
	
	
	$('input[name=\'shop_name\']').keyup(function(){
		$('input[name=\'shop_id\']').val('');
	})
	
	$('input[name=\'reviewed_by\']').devbridgeAutocomplete({
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
				$('input[name=\'reviewed_by\']').val(suggestion.value);
				$('input[name=\'reviewed_by_id\']').val(suggestion.data);
        	 	
    	 }
	});
	
	
	$('input[name=\'reviewed_by\']').keyup(function(){
		$('input[name=\'reviewed_by_id\']').val('');
	})
})
function saveReviewData(id, txtObj){
		var newValue = txtObj.value;
		var data = "id="+id+"&val="+newValue;
		data += '&outmode=json&is_ajax_request=yes';
		var href=generateUrl('reviews', 'update_ajax');
		callAjax(href, data, function(t){
			var ans = parseJsonData(t);
			if (ans === false){
				ShowJsSystemMessage('Oops! There is some Error',true,true)
				return false;
			}
			ShowJsSystemMessage(ans.msg)
			if(ans.status == 0) {
				return false;
			}
			//$('#status'+id).html(t).fadeIn().delay(1000).fadeOut();
		})
 } 
	 
function searchReviews(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#reviews-list')); 
	callAjax(generateUrl('reviews', 'listReviews'), data, function(t){ //alert(t);
		$('#reviews-list').html(t);
		applyRating();
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchReviews(frm);
}
$(document).ready(function(){
		searchReviews(document.frmReviewSearch);
});
  
function clearSearch() {
	document.frmReviewSearch.reset();
	$("#frmReviewSearch input[type=hidden]").val("");
	searchReviews(document.frmReviewSearch);
}
function applyRating(){
	$('.ratings').raty({
			score: function() {
			return $(this).attr('data-score');
		},
		readOnly: true,
		space : false,
		width : 100,
	});
}