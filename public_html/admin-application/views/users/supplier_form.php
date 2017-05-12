<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $conf_supplier_form_field_types;?> 
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
                        <div class="sectionhead"><h4>Seller Approval Form</h4></div>
						
                        <div class="sectionbody">                            
							<form class="web_form" action="?" method="post">
                            <div class="box_content clearfix toggle_container">
                            	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
									<tr>
									<td colspan="2">
										<table id="supplier_form_table" class="table_listing">
											<thead>
												<tr>
													<th width="17%">Type</th>
													<th width="17%">Caption</th>
													<th width="17%">Help Text</th>
													<th width="15%">Required</th>
													<th width="20%">Display Order</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
											<?php $field_row = 0; ?>
											<?php foreach ($supplier_form_fields as $field) { ?>
											<input type="hidden" name="form_field[<?php echo $field_row; ?>][id]" value="<?php echo $field['sformfield_id']; ?>" />
											<input type="hidden" name="form_field[<?php echo $field_row; ?>][mandatory]" value="<?php echo $field['sformfield_mandatory']; ?>" />
											<tr id="field-row<?php echo $field_row; ?>">
											<td><input type="text" autocomplete="off" name="form_field[<?php echo $field_row; ?>][type_name]" value="<?php echo $conf_supplier_form_field_types[$field["sformfield_type"]]?>" placeholder="<?php echo Utilities::getLabel('M_Type')?>" /><input type="hidden" name="form_field[<?php echo $field_row; ?>][type]" value="<?php echo $field["sformfield_type"]?>" /></td>
											<td><input type="text" autocomplete="off" name="form_field[<?php echo $field_row; ?>][caption]" value="<?php echo $field["sformfield_caption"]?>" placeholder="<?php echo Utilities::getLabel('M_Caption')?>" /></td>
											<td><input type="text" autocomplete="off" name="form_field[<?php echo $field_row; ?>][extra]" value="<?php echo $field["sformfield_extra"]?>" placeholder="<?php echo Utilities::getLabel('M_Extra_Comments')?>" /></td>
											<td><select name="form_field[<?php echo $field_row; ?>][required]" >
									<?php if ($field['sformfield_required']) { ?>
									<option value="1" selected="selected">Yes</option>
									<option value="0">No</option>
									<?php } else { ?>
									<option value="1">Yes</option>
									<option value="0" selected="selected">No</option>
									<?php } ?>
								  </select></td>
											<td><input type="text" autocomplete="off" name="form_field[<?php echo $field_row; ?>][order]" value="<?php echo $field["sformfield_order"]?>" placeholder="<?php echo Utilities::getLabel('M_Display_Order')?>" /></td>
											<td>
											<?php if ($field["sformfield_mandatory"]!=1):?>
											<ul class="actions"><li><a class="button red medium" onclick="$('#field-row<?php echo $field_row; ?>').remove();"  title="Remove"><i class="ion-minus icon"></i></a></li></ul>
											<?php endif;?>
											</td>
											</tr>
											<?php $field_row++; ?>
											
											
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="5"></td>
											<td ><ul class="actions"><li><a onclick="addFormField();" class="button medium blue" title="Add Field"><i class="ion-plus-round icon"></i></a></li></ul></td>
											</tr>
											</tfoot>
										</table>
									</td>
									</tr>
									<tr>
										<td colspan="2"><input type="submit" value="Submit" /></td>										
									</tr>
									</table>
									
	                        </div>
                            
                            </form>                        
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
var field_row = <?php echo $field_row; ?>;
function addFormField() {
	
    html  = '<tr id="field-row' + field_row + '">';
	html += '  <td><input type="text" name="form_field[' + field_row + '][type_name]" value="" placeholder="Field Type" autocomplete="off" /><input type="hidden" name="form_field[' + field_row + '][type]" value="" /></td>';
	html += '  <td><input type="text" name="form_field[' + field_row + '][caption]" value="" placeholder="Caption" autocomplete="off" /></td>';
	html += '  <td><input type="text" name="form_field[' + field_row + '][extra]" value="" placeholder="Extra Comments" autocomplete="off" /></td>';
	html += '  <td>';
	html += '<select  name="form_field[' + field_row + '][required]">';
	html += '<option value="1" selected="selected">Yes</option>';
	html += '<option value="0">No</option>';
	html += '</select>';
	html += '</td>';
	html += '<td>';
	html += '<input type="text" name="form_field[' + field_row + '][order]" value="" placeholder="Display Order" />';
	html += '</td>';
	//html += '  <td><a class="button medium red" onclick="$(\'#field-row' + field_row + '\').remove();" title="Remove" ></a></td>';
	html += '  <td><ul class="actions"><li><a class="button red medium" onclick="$(\'#field-row' + field_row + '\').remove();"  title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	$('#supplier_form_table tbody').append(html);
	formfieldautocomplete(field_row);
	field_row++;
	/*
    html  = '<tr id="option-value-row' + field_row + '">';
	html += '<td><input type="text" name="form_field[' + field_row + '][name]" value="" placeholder="Option Value Name" /></td>';
	html += '<td><input type="file" name="form_field[' + field_row + '][image]" /></td>';
	html += '<td><input type="text" name="form_field[' + field_row + '][sort]" value="" placeholder="Display Order" /></td>';
	html += '  <td><a class="button medium red" onclick="$(\'#option-value-row' + field_row + '\').remove();" title="Remove" >Remove</a></td>';
    html += '</tr>';
	$('#supplier_form_table tbody').append(html);
	field_row++;
*/}
function formfieldautocomplete(field_row) {
	
	$('input[name=\'form_field[' + field_row + '][type_name]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('users', 'supplier_form_fields_autocomplete'),
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
				$('input[name=\'form_field[' + field_row + '][type_name]\']').val(suggestion.value);
				$('input[name=\'form_field[' + field_row + '][type]\']').val(suggestion.data);
        	 	
    	 }
	});
		
	
}
$('#supplier_form_table tbody tr').each(function(index, element) {
	formfieldautocomplete(index);
});
</script> 