<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ;?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<ul class="breadcrumb flat">
			<li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
            <li>Settings</li>
			<li>Commission Settings</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Commission Settings </h4> 
	                        <ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                        	<li><a rel="fancy_popup_box" href="<?php echo Utilities::generateUrl('commissions', 'how_it_works'); ?>">How Commission Setting Works</a></li>
                                            <li><a href="<?php echo Utilities::generateUrl('commissions', 'trashed'); ?>">Trashed Settings</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            
                        </div>
						
                        <div class="sectionbody">                            
							<?php echo $frm->getFormTag ();  ?>
                            <div class="box_content clearfix toggle_container">
                            	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
									<tr>
									<td colspan="2">
										<table id="commission_form_table" class="table_listing table">
											<thead>
												<tr>
													<th width="25%">Category</th>
													<th width="25%">Vendor</th>
													<th width="25%">Product</th>
													<th width="15%">Fees [%]</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
											<?php $comm_row = 0; ?>
											<?php foreach ($commission_settings as $commsetting) { $comm_row=$commsetting['commsetting_id']; ?>
											<tr id="comm-row<?php echo $commsetting['commsetting_id']; ?>">
												<td><?php echo Utilities::displayNotApplicable($commsetting["category_name"])?></td>
												<td><?php echo Utilities::displayNotApplicable($commsetting["vendor"])?></td>
												<td><?php echo Utilities::displayNotApplicable($commsetting["prod_name"])?></td>
        	                                    <td><?php echo $commsetting["commsetting_fees"]?></td>
												<td>
												<?php if ($commsetting["commsetting_is_mandatory"]!=1):?>
											<ul class="actions"><li><a class="button red medium delete" id="<?php echo $commsetting['commsetting_id']; ?>"  title="Remove"><i class="ion-minus icon"></i></a></li></ul>
												<?php endif;?>
											</td>
											</tr>
											<?php $comm_row++; ?>
											
											
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="4"></td>
											<td ><ul class="actions"><li><a onclick="addFees();" class="button medium blue" title="Add Fees"><i class="ion-plus-round icon"></i></a></li></ul></td>
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
                            <?php echo $frm->getExternalJS();?>
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
var comm_row = <?php echo $comm_row; ?>;
function addFees() {
	
    html  = '<tr id="comm-row' + comm_row + '">';
	html += '  <td><input type="text" name="commission_setting[' + comm_row + '][category]" value="" placeholder="Category" autocomplete="off" /><input type="hidden" name="commission_setting[' + comm_row + '][category_id]" value="" /></td>';
	html += '  <td><input type="text" name="commission_setting[' + comm_row + '][vendor]" value="" placeholder="Vendor" autocomplete="off" /><input type="hidden" name="commission_setting[' + comm_row + '][vendor_id]" value="" /></td>';
	html += '  <td><input type="text" name="commission_setting[' + comm_row + '][product]" value="" placeholder="Product" autocomplete="off" /><input type="hidden" name="commission_setting[' + comm_row + '][product_id]" value="" /></td>';
	html += '<td>';
	html += '<input data-fld="requiredint" title="Commission Percentage" type="text" name="commission_setting[' + comm_row + '][fees]" value="" placeholder="Commission Percentage" />';
	html += '</td>';
	html += '  <td><ul class="actions"><li><a class="button red medium" onclick="deleteCommissionRow('+comm_row+');"  title="Remove"><i class="ion-minus icon"></i></a></li></ul></td>';
    html += '</tr>';
	var appended = $('#commission_form_table tbody').append(html);
	$(appended).find(':input').each(function(){	
			var name = $(this).attr('name');
			if($(this).attr('data-fld')=="required"){
				CommissionsfrmValidator_requirements[name] = {"required":true};
			}else if($(this).attr('data-fld')=="requiredint"){
				CommissionsfrmValidator_requirements[name] = {"required":true,"integer":true};
			}
	});
	CommissionsfrmValidator.resetFields();
	formfieldautocomplete(comm_row);
	comm_row++;
}

function deleteCommissionRow(comm_row){
			if(confirm("Sure you want to remove this item ?")){
					$('#comm-row' + comm_row ).remove();
					delete CommissionsfrmValidator_requirements['commission_setting[' + comm_row + '][fees]'];
					CommissionsfrmValidator.resetFields();
			}
}


function formfieldautocomplete(comm_row) {
	$('input[name=\'commission_setting[' + comm_row + '][category]\']').keyup(function(){
		$('input[name=\'commission_setting[' + comm_row + '][category_id]\']').val('');
	})
	$('input[name=\'commission_setting[' + comm_row + '][vendor]\']').keyup(function(){
		$('input[name=\'commission_setting[' + comm_row + '][vendor_id]\']').val('');4
		$('input[name=\'commission_setting[' + comm_row + '][product_id]\']').val('');
		$('input[name=\'commission_setting[' + comm_row + '][product]\']').val('');
		
	})
	$('input[name=\'commission_setting[' + comm_row + '][product]\']').keyup(function(){
		$('input[name=\'commission_setting[' + comm_row + '][product_id]\']').val('');
	})
	
	$('input[name=\'commission_setting[' + comm_row + '][category]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'categories_autocomplete_without_level',[1],webroot),
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
				$('input[name=\'commission_setting[' + comm_row + '][category]\']').val(suggestion.value);
				$('input[name=\'commission_setting[' + comm_row + '][category_id]\']').val(suggestion.data);
    	 }
	});
	
	$('input[name=\'commission_setting[' + comm_row + '][vendor]\']').devbridgeAutocomplete({
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
				$('input[name=\'commission_setting[' + comm_row + '][vendor]\']').val(suggestion.value);
				$('input[name=\'commission_setting[' + comm_row + '][vendor_id]\']').val(suggestion.data);
    	 }
	});
	
	
	$('input[name=\'commission_setting[' + comm_row + '][product]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'products_autocomplete',[1],webroot),
				data: {keyword: encodeURIComponent(query), user_id : $('input[name=\'commission_setting[' + comm_row + '][vendor_id]\']').val() },
				dataType: 'json',
				type: 'post',
				success: function(json) {
						done(json);
					}
				});
			
	    	 },
			 triggerSelectOnValidInput: true,
	    	 onSelect: function (suggestion) {
				$('input[name=\'commission_setting[' + comm_row + '][product]\']').val(suggestion.value);
				$('input[name=\'commission_setting[' + comm_row + '][product_id]\']').val(suggestion.data);
				$('input[name=\'commission_setting[' + comm_row + '][category]\']').val('');
				$('input[name=\'commission_setting[' + comm_row + '][category_id]\']').val('');
				$('input[name=\'commission_setting[' + comm_row + '][vendor]\']').val('');
				$('input[name=\'commission_setting[' + comm_row + '][vendor_id]\']').val('');
			}
	});	
	
}
$('#commission_form_table tbody tr').each(function(index, element) {
	formfieldautocomplete(index);
});
</script> 