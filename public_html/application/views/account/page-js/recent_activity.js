var processing_activities_load = false;
$(document).ready(function() {
	
	
	searchActivities(document.frmSearch);
});
function listNextPage(){
	var frm = document.frmSearch;
	frm.page.value = parseInt(frm.page.value) + 1;
	searchActivities(frm,1);
}
var track_loaded_pages=0;
function searchActivities(frm, append){
	if(processing_activities_load == true) return false;
	processing_activities_load = true;
	var data = getFrmData(frm);
	$('#loader').remove();
	showCssElementLoading($('#activity-list'), append);
	callAjax(generateUrl('account', 'ajax_recent_activity'), data, function(t){
		track_loaded_pages++;
		var ans = parseJsonData(t);
		if(ans === false){
			processing_activities_load = false;
			return false;
		}
		if(append == 1){
			$('#loader').remove();
			$('#activity-list').append(ans.html);
		}else{
			$('#activity-list').html(ans.html);
		}
		equalHeight($(".groupbox"));
		
		total_pages=ans.total_pages;
		$(window).scroll(function() { 
			//if($(window).scrollTop() + $(window).height() >= $(document).height())  {
			if(($(window).scrollTop() + $(window).height() >= $(document).height() - $(".footer").height()))  {	
				if ((processing_activities_load==false) && (track_loaded_pages < total_pages )) {
					listNextPage();
					return;
				}
			}
		});
		processing_activities_load = false;
		return false;
	});
	return false;
}
