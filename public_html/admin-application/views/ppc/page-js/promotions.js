function searchPromotions(frm){
	var data = getFrmData(frm);
	showHtmlElementLoading($('#ppc-list'));
	//alert(data);
	var url = generateUrl('ppc', 'listPromotions');
	if (frm.s.value=='c')
		url=generateUrl('ppc', 'listPromotionClicks')
	else if (frm.s.value=='p')
		url=generateUrl('ppc', 'listPromotionPayments')
	else if (frm.s.value=='l')
		url=generateUrl('ppc', 'listPromotionLogs')		
	 
	callAjax(url, data, function(t){
		$('#ppc-list').html(t);
	});
}
function listPages(p){ 
	var frm = document.paginateForm;
	frm.page.value = p;
	searchPromotions(frm);
}
function loadPromotions(){
	searchPromotions(document.frmPromotionSearch);
}
$(document).ready(function(){
	loadPromotions();
});
  
function clearSearch() {
	document.frmPromotionSearch.reset();
	$("#frmPromotionSearch input[type=hidden]").val("");
	searchPromotions(document.frmPromotionSearch);
}
function UpdateStatus(id, el) {
	callAjax(generateUrl('ppc', 'update_status'), 'id=' + id, function(t){ 
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		if (el.parent().hasClass('enabled')) {
			el.parent().removeClass('enabled').addClass('disabled');
		}else {
			el.parent().removeClass('disabled').addClass('enabled');
		}
		el.parent().attr("title", "Click to Disable");
		if (el.parent().hasClass('disabled')) {
			el.parent().attr("title", "Click to Enable");
		}	
	});
}
function ApprovePromotion(id, el) {
	callAjax(generateUrl('ppc', 'approve'), 'id=' + id, function(t){
		var ans = parseJsonData(t);
		if (ans === false){
			ShowJsSystemMessage('Oops! There is some Error',true,true)
			return false;
		}
		ShowJsSystemMessage(ans.msg)
		if(ans.status == 0) {
			return false;
		}
		$(el).closest("li").html('<a class="toggleswitch deactivated"><i class="ion-thumbsup icon"></i></a>');
	});
}
function ViewPromotionClicks(id) {
	callAjax(generateUrl('ppc', 'listPromotionClicks'), 'id=' + id+'&page=1&s=c', function(t){
		$('#ppc-list').html(t);
	});
}
function ViewPromotionPayments(id) {
	callAjax(generateUrl('ppc', 'listPromotionPayments'), 'id=' + id+'&page=1&s=p', function(t){
		$('#ppc-list').html(t);
	});
}
function ViewPromotionLog(id) {
	callAjax(generateUrl('ppc', 'listPromotionLogs'), 'id=' + id+'&page=1&s=l', function(t){
		$('#ppc-list').html(t);
	});
}
$(function() {
	
	$('input[name=\'promotion_by\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('ppc', 'promoters_autocomplete',[1]),
				data: {keyword: encodeURIComponent(query)},
				dataType: 'json',
				type: 'post',
				success: function(json) { //alert(json);
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				 $('input[name=\'promotion_by\']').val(suggestion.value);
				 $('input[name=\'user\']').val(suggestion.data);
			 }
	});
		
	
	
	
})
