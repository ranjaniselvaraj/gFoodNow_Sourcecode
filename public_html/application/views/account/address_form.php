<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if ($ajax_request==false): ?> 
		 <div class="body clearfix">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
          <div class="fixed-container">
            <div class="dashboard">
              <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
              <div class="data-side">
              	<?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
                <h3><?php echo Utilities::getLabel('L_My_Addresses')?></h3>
                <ul class="arrowTabs">
                  <li><a href="<?php echo Utilities::generateUrl('account', 'addresses')?>"><?php echo Utilities::getLabel('L_Address_List')?></a></li>
                  <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'address_form')?>"><?php echo Utilities::getLabel('L_Add_Address')?></a></li>
                </ul>
                <div class="space-lft-right">
                    <div class="wrapform">
                        <?php echo $frmAddress->getFormHtml(); ?>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
<?php else:?> 
	
	<div class="data-side">
    				<span id="ajax_message"></span>
    	            <div class="wrapform">
                        <?php echo $frmAddress->getFormHtml(); ?>
        	        </div>
              </div>
<?php endif; ?>    
<script type="text/javascript">
$(document).ready(function(){
	loadStates(document.getElementById('ua_country'), <?php echo intval($frmAddress->getField('ua_state')->value); ?>);
});
</script>