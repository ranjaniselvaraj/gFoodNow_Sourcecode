<?php 
	defined('SYSTEM_INIT') or die('Invalid Usage');
	if(isset($stripe)){
		if(isset($stripe['secret_key']) && isset($stripe['publishable_key'])){
			if(!empty($stripe['secret_key']) && !empty($stripe['publishable_key'])){
				?>
				<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
				<script type="text/javascript">
					var publishable_key = '<?php echo $stripe['publishable_key']; ?>';
				</script>
				<?php 
			}
		}
	}
?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount)?></strong> </p>
	  <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"])?></strong> </p>
    </div>
    <div class="payment-from">
		<p id="paymentStatus"></p>
        <div id="ajax_message"></div>
		<?php 
			if (isset($error))
				echo '<div class="alert alert-danger">'.$error.'</div>';
			if(isset($success))
				echo '<div class="alert alert-success">Your payment has been successfull.</div>';
			
			if(isset($frm))
				echo  $frm->getFormHtml();
		?>
    </div>
  </div>
</div>
