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
			<li>Affiliate Commission Settings</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Affiliate Commission Settings </h4> 
	                        <ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('affiliatecommissions', 'trashed'); ?>">Trashed Settings</a></li>
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
													<th width="35%">Category</th>
													<th width="35%">Affiliate</th>
													<th width="20%">Commission [%]</th>
													<th></th>
												</tr>
											</thead>
											<tbody>
											<?php $comm_row = 0; ?>
											<?php foreach ($commission_settings as $commsetting) { $comm_row=$commsetting['afcommsetting_id']; ?>
											<tr id="comm-row<?php echo $commsetting['afcommsetting_id']; ?>">
												<td><?php echo Utilities::displayNotApplicable($commsetting["category_name"])?></td>
												<td><?php echo Utilities::displayNotApplicable($commsetting["affiliate"])?></td>
        	                                    <td><?php echo $commsetting["afcommsetting_fees"]?></td>
												<td>
												<?php if ($commsetting["afcommsetting_is_mandatory"]!=1):?>
											<ul class="actions"><li><a class="button red medium delete" id="<?php echo $commsetting['afcommsetting_id']; ?>"  title="Remove"><i class="ion-minus icon"></i></a></li></ul>
												<?php endif;?>
											</td>
											</tr>
											<?php $comm_row++; ?>
											
											
											<?php } ?>
											</tbody>
											<tfoot>
											<tr>
											<td colspan="3"></td>
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
	html += '  <td><input type="text" name="commission_setting[' + comm_row + '][affiliate]" value="" placeholder="Affiliate" autocomplete="off" /><input type="hidden" name="commission_setting[' + comm_row + '][affiliate_id]" value="" /></td>';
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
	//$('#commission_form_table tbody').append(html);
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
	$('input[name=\'commission_setting[' + comm_row + '][affiliate]\']').keyup(function(){
		$('input[name=\'commission_setting[' + comm_row + '][affiliate_id]\']').val('');
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
	
	$('input[name=\'commission_setting[' + comm_row + '][affiliate]\']').devbridgeAutocomplete({
			 minChars:0,
			 //autoSelectFirst:true,	
			 lookup: function (query, done) {
				$.ajax({
				url: generateUrl('common', 'affiliates_autocomplete',[],webroot),
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
				$('input[name=\'commission_setting[' + comm_row + '][affiliate]\']').val(suggestion.value);
				$('input[name=\'commission_setting[' + comm_row + '][affiliate_id]\']').val(suggestion.data);
    	 }
	});
	
	
}
$('#commission_form_table tbody tr').each(function(index, element) {
	formfieldautocomplete(index);
});
</script> 