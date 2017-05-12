<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if ($ajax_request==false): ?> 
		 <div class="body clearfix">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
          <div class="fixed-container">
            <div class="dashboard">
              <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
              <div class="data-side">
              	<?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
                <h3><?php echo Utilities::getLabel('M_Options_Variants')?></h3>
                <ul class="arrowTabs">
                  <li><a href="<?php echo Utilities::generateUrl('account', 'options')?>"><?php echo Utilities::getLabel('M_Options_List')?></a></li>
             	  <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'option_form')?>"><?php echo Utilities::getLabel('L_Add_Option')?></a></li>
                </ul>
                <div class="space-lft-right">
                    <div class="wrapform">
                        <?php echo $frm->getFormHtml(); ?>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
<?php else:?> 

	<div class="data-side">
    <div class="box-head">
        <h4><?php echo Utilities::getLabel('M_Options_Variants')?></h4>
    </div>
    <span id="ajax_message"></span>
    <div class="wrapform">
	     <?php echo $frm->getFormHtml(); ?>
   	</div>
</div>
	
<?php endif; ?>    
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


function addOptionValue() {
    html  = '<tr id="option-value-row' + option_value_row + '">';
	html += '<td><input data-fld="name" type="text" name="option_values[' + option_value_row + '][name]" value="" title="<?php echo Utilities::getLabel('F_Option_Value_Name')?>" placeholder="<?php echo Utilities::getLabel('F_Option_Value_Name')?>" /></td>';

	html += '<td><input type="text" name="option_values[' + option_value_row + '][sort]" value="" placeholder="<?php echo Utilities::getLabel('F_Display_Order')?>" /></td>';
	html += '  <td><button type="button" class="btn red" onclick="deleteOptionValue('+option_value_row+');" title="<?php echo Utilities::getLabel('M_Remove')?>" ><i><img src="'+webroot+'images/minus-white.png" alt=""/></i></button></td>';
    html += '</tr>';
	var appended = $('#option_value tbody').append(html);
		$(appended).find(':input').each(function(){	
			var name = $(this).attr('name');
			if($(this).attr('data-fld')!=undefined && $(this).attr('data-fld')!=''){
					OptionfrmValidator_requirements[name] = OptionfrmValidator_requirements['option_type'];
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
