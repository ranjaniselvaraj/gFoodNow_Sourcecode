<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('L_Request_Withdrawal')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('account', 'credits')?>" class="btn small">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_credit_summary')?></a> </div>
          </div>
          
          <div>
          	
            <div class="mycredits space-lft-right">
            	<div class="alert alert-info"><?php echo Utilities::getLabel('L_Withdrawable_Balance_Desc')?></div>
                <div class="largebox">
                  <div class="crr-blnc"><?php echo Utilities::getLabel('L_Withdrawable_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($final_withdrawable_balance)?></strong> </div>
                  <!--<a class="btn" href="<?php echo Utilities::generateUrl('account','request_withdrawal')?>"><?php echo Utilities::getLabel('L_Request_Withdrawal')?></a> </div>-->
	         	</div>
          	</div>
          <div class="gap"></div>
          <div class="space-lft-right">
	        <div class="wrapform">
				<?php echo $frmWithdrawalInfo->getFormHtml(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  