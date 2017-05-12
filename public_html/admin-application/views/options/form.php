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
                        <div class="sectionhead"><h4>Options Setup</h4></div>
						
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
$('select[name=\'option_type\']').on('change', function() {
	if (this.value == 'select' || this.value == 'radio' || this.value == 'checkbox' || this.value == 'image') {
		$('#option_value').show();
	} else {
		$('#option_value').hide();
	}
});
$('select[name=\'option_type\']').trigger('change');
var option_value_row = <?php echo $option_value_row; ?>;
/*function addOptionValue() {
    html  = '<tr id="option-value-row' + option_value_row + '">';
	html += '<td><input type="text" name="option_values[' + option_value_row + '][name]" value="" placeholder="Option Value Name" /></td>';
	html += '<td><input type="text" name="option_values[' + option_value_row + '][sort]" value="" placeholder="Display Order" /></td>';
	html += '  <td><ul class="actions"><li><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	$('#option_value tbody').append(html);
	option_value_row++;
}*/
function addOptionValue() {
    html  = '<tr id="option-value-row' + option_value_row + '">';
	html += '<td><input data-fld="name" title="Option value name" type="text" name="option_values[' + option_value_row + '][name]" value="" placeholder="Option Value Name" /></td>';
	html += '<td><input type="text" name="option_values[' + option_value_row + '][sort]" value="" placeholder="Display Order" /></td>';
	html += '  <td><ul class="actions"><li><a onclick="deleteOptionValue('+option_value_row+');" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	var appended = $('#option_value tbody').append(html);
		$(appended).find(':input').each(function(){	
			var name = $(this).attr('name');
			if($(this).attr('data-fld')!=undefined && $(this).attr('data-fld')!=''){
					OptionfrmValidator_requirements[name] =OptionfrmValidator_requirements['option_type'];
			} 
		});
	OptionfrmValidator.resetFields();
	option_value_row++;
}
function deleteOptionValue(option_value_row){
	$('#option-value-row' + option_value_row ).remove();
	delete OptionfrmValidator_requirements['option_values[' + option_value_row + '][name]'];
	OptionfrmValidator.resetFields();
}
$(document).ready(function(){
	$("#option_value").find(':input').each(function(){
			var name = $(this).attr('name');
			if($(this).attr('data-fld')!=undefined && $(this).attr('data-fld')!=''){
					OptionfrmValidator_requirements[name] =OptionfrmValidator_requirements['option_type'];
			} 
			
		});
	OptionfrmValidator.resetFields();
});
</script>						