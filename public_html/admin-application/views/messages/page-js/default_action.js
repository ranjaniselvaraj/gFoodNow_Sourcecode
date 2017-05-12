function searchMessages(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#messages-list')); 
	callAjax(generateUrl('messages', 'listmessages'), data, function(t){
		$('#messages-list').html(t);
	});
}
function listPages(p){
	var frm = document.paginateForm;
	frm.page.value = p;
	searchMessages(frm);
}
$(document).ready(function(){
		searchMessages(document.frmMessagesSearch);
});
  
function clearSearch() {
	document.frmMessagesSearch.reset();
	$("#frmMessagesSearch input[type=hidden]").val("");
	searchMessages(document.frmMessagesSearch);
}
 
$(function() {
	
	$('input[name=\'message_by\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'users_autocomplete',[1],webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { 
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'message_by\']').val(suggestion.value);
				$('input[name=\'from\']').val(suggestion.data);
    	 }
	});
	
	
	$('input[name=\'message_to\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'users_autocomplete',[1],webroot),
				data: {keyword: encodeURIComponent(query) },
				dataType: 'json',
				type: 'post',
				success: function(json) { 
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'message_to\']').val(suggestion.value);
				$('input[name=\'to\']').val(suggestion.data);
    	 }
	});
	
	
	
	$('input[name=\'message_by\']').keyup(function(){
		$('input[name=\'from\']').val('');
	})
	$('input[name=\'message_to\']').keyup(function(){
		$('input[name=\'to\']').val('');
	})
})
	 