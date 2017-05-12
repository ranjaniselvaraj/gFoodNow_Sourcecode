<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_Shop_Information')?></h3>
            <div class="space-lft-right">
	             <? if (!$shop_is_deleted) {?>
		        	<div class="wrapform">
        			<?php echo $frmShopInfo->getFormHtml(); ?>
            	</div>
	            <? } ?> 
          	</div>
        </div>
      </div>
    </div>
  </div>
 
<script type="text/javascript">
$(document).ready(function(){
	loadStates(document.getElementById('ua_country'), <?php echo intval($frmShopInfo->getField('ua_state')->value); ?>);
});
</script>