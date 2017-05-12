<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<?php echo html_entity_decode($breadcrumb); ?>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Filters Setup</h4></div>
						
                        <div class="sectionbody">                            
                             <?php echo $frm->getFormHtml(); ?>                        
						</div>	
															
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>
<script type="text/javascript">
var filter_group_value_row = <?php echo $filter_group_value_row; ?>;
function addFilterValue() {
    html  = '<tr id="filter-group-value-row' + filter_group_value_row + '">';
	html += '<td><input type="text" name="filter_group_values[' + filter_group_value_row + '][name]" value="" placeholder="Filter Name" /></td>';
	html += '<td><input type="text" name="filter_group_values[' + filter_group_value_row + '][sort]" value="" placeholder="Display Order" /></td>';
	html += '  <td><ul class="actions"><li><a onclick="$(\'#filter-group-value-row' + filter_group_value_row + '\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	$('#filter_group_value tbody').append(html);
	filter_group_value_row++;
}
</script>						