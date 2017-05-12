<!-- load Google AJAX API -->																	
<script type="text/javascript">
var w=$('.tabs_panel_wrap').width();
google.load('visualization', '1', {'packages':['corechart', 'bar']});
//set callback
google.setOnLoadCallback (createChart);
   //callback function
   function createChart() {
	//create data table object
	var dataTable = new google.visualization.DataTable();
	//define columns for first example
	dataTable.addColumn('string','Duration');
	dataTable.addColumn('number', 'Sales');
	//define rows of data for first example
	dataTable.addRows([<?php echo html_entity_decode($dashboard_info['sales_chart_data'],ENT_QUOTES,'UTF-8');?>]);
	//instantiate our chart objects
	var saleChart = new google.visualization.ColumnChart (document.getElementById('monthlysales'));	//define options for visualization	
	var options = {width: w, height: 240, title: 'Order Sales: Last 12 months',hAxis: {title: 'Duration'},vAxis: { title: 'Sales'}};
		<?php if($configuredAnalytics){?>	// Visitors Statistics	
		var dataVisits = google.visualization.arrayToDataTable(<?php echo html_entity_decode($dashboard_info['visits_chart_data'],ENT_QUOTES,'UTF-8');?>);
	var optionVisits = { title: 'Visitor Stats',width:$('#visitsGraph').width(),height:300,curveType: 'function',legend: { position: 'bottom' } };	
	var visits = new google.visualization.LineChart(document.getElementById('visitsGraph'));
		<?php }?>
	//Sales Earnings
	var dataTableSalesEearnings = new google.visualization.DataTable();
	dataTableSalesEearnings.addColumn('string','Duration');
	dataTableSalesEearnings.addColumn('number', 'Earnings');	//define rows of data for first example	
	dataTableSalesEearnings.addRows([<?php echo html_entity_decode($dashboard_info['sales_earnings_chart_data'],ENT_QUOTES,'UTF-8');?>]);
	var view = new google.visualization.ColumnChart(document.getElementById('monthlysalesearnings'));	//define options for visualization	
	var optionsView = {width: w, height: 240,  title: 'Sales Earnings: Last 12 months',hAxis: {title: 'Duration'},vAxis: { title: 'Sales Earnings'}};	
	//Signup  	
	var dataTableSignUp = new google.visualization.DataTable();
	dataTableSignUp.addColumn('string','Duration');
	dataTableSignUp.addColumn('number', 'Signups');
	dataTableSignUp.addRows([<?php echo html_entity_decode($dashboard_info['signups_chart_data'],ENT_QUOTES,'UTF-8');?>]);
	var viewSignup = new google.visualization.ColumnChart(document.getElementById('monthly-signups'));	var optionsSignUp = {width: w, height: 240,  title: 'Signups: Last 12 months',hAxis: {title: 'Duration'},vAxis: { title: 'Signups'}};		
	
	//Signup
	var dataTableAffiliateSignUp = new google.visualization.DataTable();
	dataTableAffiliateSignUp.addColumn('string','Duration');
	dataTableAffiliateSignUp.addColumn('number', 'Signups');
	dataTableAffiliateSignUp.addRows([<?php echo html_entity_decode($dashboard_info['affiliate_signups_chart_data'],ENT_QUOTES,'UTF-8');?>]);
	var viewAffiliateSignup = new google.visualization.ColumnChart(document.getElementById('affiliate-monthly-signups'));
	var optionsAffiliateSignUp = {width: w, height: 240,  title: 'Affiliate Signups: Last 12 months',hAxis: {title: 'Duration'},vAxis: { title: 'Affiliate Signups'}};	
	//Signup  	
	var dataTableProducts = new google.visualization.DataTable();
	dataTableProducts.addColumn('string','Duration');
	dataTableProducts.addColumn('number', 'Products');
	dataTableProducts.addRows([<?php echo html_entity_decode($dashboard_info['products_chart_data'],ENT_QUOTES,'UTF-8');?>]);
	var viewProdcuts = new google.visualization.ColumnChart(document.getElementById('products-listed'));	var optionsProducts = {width: w,height: 240,title: 'Products: Last 12 months',hAxis: {title: 'Duration'},vAxis: { title: 'Products'}
	}; 
	
	<?php if($configuredAnalytics){?>
	// Conversions Statistics
	var dataConversion = google.visualization.arrayToDataTable([<?php echo html_entity_decode($dashboard_info['conversion_chat_data'],ENT_QUOTES,'UTF-8');?>]);
	var optionConversion = { width:$('#conversionStats').width(),height:240,'color':'#AEC785',legend: { position: "none" }};
	var conversion = new google.visualization.ColumnChart(document.getElementById('conversionStats'));
	<?php }?>
	
	//draw our chart charts
	saleChart.draw(dataTable, options);	
	view.draw(dataTableSalesEearnings, optionsView);
	viewSignup.draw(dataTableSignUp, optionsSignUp);
	viewProdcuts.draw(dataTableProducts, optionsProducts);
	viewAffiliateSignup.draw(dataTableAffiliateSignUp, optionsAffiliateSignUp);
	<?php if($configuredAnalytics){?>
		visits.draw(dataVisits, optionVisits); 
		conversion.draw(dataConversion, optionConversion);
	<?php }?>
}	
</script>