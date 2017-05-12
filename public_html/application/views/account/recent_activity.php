<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_Recent_Activity')?></h3>
          <?php echo $frmSearch->getFormHtml(); ?>
          <ul class="threeCols scroll">
          	<span id="activity-list"></span>
          </ul>
        </div>
        
      </div>
    </div>
  </div>
