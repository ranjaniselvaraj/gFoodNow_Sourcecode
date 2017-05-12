<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_Account_Information')?></h3>
          <ul class="arrowTabs">
	             <li><a href="<?php echo Utilities::generateUrl('account', 'profile_info')?>"><?php echo Utilities::getLabel('L_Personal_Information')?></a></li>
                 <li><a href="<?php echo Utilities::generateUrl('account', 'bank_info')?>"><?php echo Utilities::getLabel('L_Bank_Account_Info')?></a></li>
                <!-- <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'return_info')?>"><?php echo Utilities::getLabel('M_Return_Address_Info')?></a></li>-->
          </ul>
          <div class="space-lft-right">
	        <div class="wrapform">
				<?php echo $frmReturnInfo->getFormHtml(); ?>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
$(document).ready(function(){
	loadStates(document.getElementById('ua_country'), <?php echo intval($frmReturnInfo->getField('ua_state')->value); ?>);
});
</script>  