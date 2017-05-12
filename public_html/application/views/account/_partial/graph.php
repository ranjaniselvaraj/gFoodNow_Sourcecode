<!-- load Google AJAX API -->																	
<script type="text/javascript">

google.load('visualization', '1', {'packages':['corechart', 'bar']});
//set callback
google.setOnLoadCallback (createChart);
function createChart() { 
	var w =$('#graph_parent').width();
	//Sales Earnings 
	var dataTableSalesEearnings = new google.visualization.DataTable();
	dataTableSalesEearnings.addColumn('string','<?php echo Utilities::getLabel('L_Duration');?>');
	dataTableSalesEearnings.addColumn('number', '<?php echo Utilities::getLabel('L_Earnings');?>');
	
	//define rows of data for first example
	dataTableSalesEearnings.addRows([<?php echo html_entity_decode($dashboard_info['sales_earnings_chart_data'],ENT_QUOTES,'UTF-8');?>]);
	var view = new google.visualization.ColumnChart(document.getElementById('monthlysalesearnings'));
	//define options for visualization
	var optionsView = {width: w, height: 380,  title: '<?php echo Utilities::getLabel('L_Sales_Earnings_Last_12_Months');?>',hAxis: {title: '<?php echo Utilities::getLabel('L_Duration');?>'},
	vAxis: { title: '<?php echo Utilities::getLabel('L_Sales_Earnings');?>'}};
	
	view.draw(dataTableSalesEearnings, optionsView);
}   
</script>