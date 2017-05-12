<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount)?></strong> </p>
      <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"])?></strong> </p>
      
    </div>
    <div class="payment-from">
			<?php if (!isset($error)): ?>	
            <?php echo  $frm->getFormHtml(); ?>
            <?php else: ?>
            <div class="alert alert-danger"><?php echo $error?><div>
            <?php endif;?>
            <div id="ajax_message"></div>
    </div>
  </div>
</div>
<script type="text/javascript">
window.onload = function(){
  document.forms['frmPaymentForm'].submit();
}
</script>
</body>
</head>