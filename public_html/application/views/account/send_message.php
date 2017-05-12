<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('F_Send_a_message')?></h3>
          </div>
          <div class="space-lft-right">
	        <div class="wrapform">
				<?php echo $frm->getFormHtml(); ?>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
  