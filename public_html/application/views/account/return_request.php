<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('L_Request_Return_Replace')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('account', 'orders')?>" class="btn small ">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_my_orders')?></a> </div>
          </div>
          <div class="space-lft-right">
	        <div class="wrapform">
				<?php echo $frmReturnInfo->getFormHtml(); ?>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
  