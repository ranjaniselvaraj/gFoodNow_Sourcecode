<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="sell-form">
	          <h3><?php echo Utilities::getLabel('L_Seller_Profile_Activation')?></h3>
        	  <div class="space-lft-right">
	            <h4><?php echo Utilities::getLabel('L_PLEASE_SUBMIT_YOUR_BUSINESS_INFO')?></h4>
		        <div class="wrapform">
					<?php echo $frmSupplierForm->getFormHtml(); ?>
        	    </div>
          	</div>
           </div> 
        </div>
        
      </div>
    </div>
  </div>
  