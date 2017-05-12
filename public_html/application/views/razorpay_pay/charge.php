<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount)?></strong> </p>
      <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"])?></strong> </p>
      
    </div>
    <div class="payment-from">
			<?php  if (!isset($error)): ?>
                       <p>Please click on payment button below to proceed with the payments..</p>
			<?php echo  $frm->getFormHtml() ?>
			<?php  else: ?>
				  <div class="alert alert-danger"><?php echo $error?><div>
			<?php  endif;?>
            <div class="gap"></div>
            <input type="submit" onclick="razorpaySubmit(this);" value="<?php echo Utilities::getLabel('L_Confirm_Payment')?>" class="btn btn-primary" />
    </div>
  </div>
</div>
<script type="text/javascript">
    $(function(){
		//setTimeout(function(){ $('form[name="razorpay-form"]').submit() }, 3000);
	})
</script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  var razorpay_options = {
    key: "<?php echo $payment_settings['merchant_key_id']; ?>",
    amount: "<?php echo $payment_amount*100; ?>",
    name: "<?php echo $order_info["site_system_name"]; ?>",
    description: "<?php echo sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice'])?>",
    netbanking: true,
    currency: "INR",
    prefill: {
      name:"<?php echo $order_info["customer_name"]; ?>",
      email: "<?php echo $order_info["customer_email"]; ?>",
      contact: "<?php echo $order_info["customer_phone"]; ?>"
    },
    notes: {
      system_order_id: "<?php echo $order_info["id"];; ?>"
    },
    handler: function (transaction) {
        document.getElementById('razorpay_payment_id').value = transaction.razorpay_payment_id;
        document.getElementById('razorpay-form').submit();
    }
  };
  var razorpay_submit_btn, razorpay_instance;
  function razorpaySubmit(el){
    if(typeof Razorpay == 'undefined'){
      setTimeout(razorpaySubmit, 200);
      if(!razorpay_submit_btn && el){
        razorpay_submit_btn = el;
        el.disabled = true;
        el.value = 'Please wait...';  
      }
    } else {
      if(!razorpay_instance){
        razorpay_instance = new Razorpay(razorpay_options);
        if(razorpay_submit_btn){
          razorpay_submit_btn.disabled = false;
          razorpay_submit_btn.value = "<?php echo $button_confirm; ?>";
        }
      }
      razorpay_instance.open();
    }
  }
</script>	
