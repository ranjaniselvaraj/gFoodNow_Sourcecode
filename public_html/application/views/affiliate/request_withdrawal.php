<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <div class="box-head">
            <h3><?php echo Utilities::getLabel('L_Request_Withdrawal')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('affiliate', 'credits')?>" class="btn small">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_credit_summary')?></a> </div>
          </div>
          <div>
          	<div class="mycredits space-lft-right">
                <div class="box">
                  <div class="crr-blnc"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($affiliate_details["balance"])?></strong> </div>
                  
	         </div>
            <!--<div class="mycredits"> <span class="highlighted_text"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($affiliate_details["balance"])?></strong> </span> </div>-->
          </div>
          
          <div class="gap"></div>
          <div class="wrapform">
				<?php echo $frmWithdrawalInfo->getFormHtml(); ?>
            </div>
          
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
$(document).ready(function(){
	$('input[name=\'affiliate_payment\']').on('change', function() {
		$('.payment').parent().parent().hide();
		$('div [lang=\'payment-'+this.value+'\']').parent().parent().show();
	});
	$('input[name=\'affiliate_payment\']:checked').trigger('change');
	
});
</script>  