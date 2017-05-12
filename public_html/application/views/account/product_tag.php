<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="data-side">
    <div class="box-head">
        <h4><?php echo Utilities::getLabel('L_Create_Your_Own_Product_Tag')?></h4>
    </div>
    <span id="ajax_message"></span>
    <div class="wrapform">
	    <?php echo $frmProductTag->getFormHtml(); ?>
   	</div>
</div>
<script type="text/javascript">
function validateRequestForm(frm,v){
		HideJsSystemMessage();
		var me=$(this);
		if ( me.data('requestRunning') ) {
			return;
		}
		me.data('requestRunning', true);
				var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(logged) {
							if (logged==true){
								v.validate();
								if(!v.isValid()) {
									me.data('requestRunning', false);
									return;
								}
								var data = getFrmData(frm);
								data += '&outmode=json&is_ajax_request=yes';
								var href=generateUrl('account', 'product_tag');
								callAjax(href, data, function(response){
									me.data('requestRunning', false);
									var json = parseJsonData(response);
									ShowJsSystemMessage(json['msg']);
									$("#frmTagForm").clearForm();
								})
								
							}else{  login_popupbox(); }
							me.data('requestRunning', false); 
					});
		return false;					
	}
</script>  