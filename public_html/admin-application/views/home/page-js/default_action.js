$(document).ready(function() { 
	 // tabbed content
    $(".tabs_content").hide();
    $(".tabs_content:first").show();
	  /* if in tab mode */
    	$(".detailTabs li a").click(function(event) {
		      $(".tabs_content").hide();
	      var activeTab = $(this).attr("rel"); 
    	  $("#"+activeTab).fadeIn();		
	      $(".detailTabs li").removeClass("active");
    	  $(this).parent().addClass("active");
    });
	
	$(".summarytabs_content").hide();
    $(".summarytabs_content:first").show();
	  /* if in tab mode */
    	$(".summarydetailTabs li a").click(function(event) {
		      $(".summarytabs_content").hide();
	      var activeTab = $(this).attr("rel"); 
    	  $("#"+activeTab).fadeIn();		
	      $(".summarydetailTabs li").removeClass("active");
    	  $(this).parent().addClass("active");
    });		
});
function topReferers(interval)
{
	$('.topReferers').html('<li>Loading...</li>');
	data="rtype=top_referrers&interval="+interval;
	callAjax(generateUrl('home','dashboard_stats'),data,function(t){					
		$('.topReferers').html(t);		
	});
}
function topCountries(interval)
{
	$('.topCountries').html('<li>Loading...</li>');
	data="rtype=top_countries&interval="+interval;
	callAjax(generateUrl('home','dashboard_stats'),data,function(t){					
		$('.topCountries').html(t);		
	});
}
function topProducts(interval)
{	
	$('.topProducts').html('<li>Loading...</li>');
	data="rtype=top_products&interval="+interval;
	callAjax(generateUrl('home','dashboard_stats'),data,function(t){					
		$('.topProducts').html(t);		
	});
}
function getTopSearchKeyword(interval)
{
	$('.topSearchKeyword').html('<li>Loading...</li>');
	data="rtype=top_search_keyword&interval="+interval;
	callAjax(generateUrl('home','dashboard_stats'),data,function(t){					
		$('.topSearchKeyword').html(t);		
	});
}
function traficSource(interval)
{
	$('#piechart').html('Loading...');
	data="rtype=traffic_source&interval="+interval;
	callAjax(generateUrl('home','dashboard_stats'),data,function(t){
		var ans=parseJsonData(t);
		var dataTraficSrc = google.visualization.arrayToDataTable(ans);
		var optionsTraficSrc = { title: 'Traffic Sources',width:$('#piechart').width(),height:360,pieHole: 0.4 };	
		var trafic = new google.visualization.PieChart(document.getElementById('piechart'));
		trafic.draw(dataTraficSrc, optionsTraficSrc); 	
	});	
}
function visitorStats()
{
	$('#visitsGraph').html('Loading...');
	data="rtype=visitors_stats";
	callAjax(generateUrl('home','dashboard_stats'),data,function(t){
		var ans=parseJsonData(t);		
		var dataVisits = google.visualization.arrayToDataTable(ans);
		var optionVisits = { title: 'Visitor Stats',width:$('#visitsGraph').width(),height:240,curveType: 'function',legend: { position: 'bottom' } };	
		var visits = new google.visualization.LineChart(document.getElementById('visitsGraph'));
		visits.draw(dataVisits, optionVisits); 	
	});
}
function conversionStats()
{	
	var dataVisits = google.visualization.arrayToDataTable([['Added to cart', 10],['Reached checkout', 15],['Purchased', 20]]);
	var optionVisits = { title: 'Conversion Stats',width:$('#conversionStats').width(),height:240,curveType: 'function',legend: { position: 'bottom' } };	
	var visits = new google.visualization.ColumnChart(document.getElementById('conversionStats'));
	visits.draw(dataVisits, optionVisits); 
}
