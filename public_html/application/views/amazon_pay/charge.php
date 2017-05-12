<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount)?></strong> </p>
	  <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"])?></strong> </p>
    </div>
    <div class="payment-from">
		<p id="paymentStatus"></p>
		<?php 
			if (isset($error))
				echo '<div class="error-wrap error"><p>'.$error.'</p></div>';
			if(isset($success))
				echo '<div class="success-message" style="color:green;"><p>Your payment has been successfull.</p></div>';
			
			if(intval($order_id) > 0 && $order_info["order_payment_status"] == 0) echo '<div class="text-center" style="margin-top:40px;" id="AmazonPayButton"></div>';
		?>
    </div>
  </div>
</div>
<?php 
	defined('SYSTEM_INIT') or die('Invalid Usage');
	if(isset($amazon) && intval($order_id) > 0 && $order_info["order_payment_status"] == 0){
		if( strlen($amazon['merchant_id']) > 0 && strlen($amazon['access_key']) > 0 && strlen($amazon['secret_key']) > 0 && strlen($amazon['client_id']) > 0 && strlen($amazon['transaction_mode']) > 0) {
			?>
			<script type="text/javascript">
				window.onAmazonLoginReady = function () {
					amazon.Login.setClientId('<?php echo $amazon['client_id']; ?>');
					amazon.Login.setUseCookie(true);
				};
			</script>
			<script type='text/javascript' src='https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js'></script>
			<script type="text/javascript">
				var authRequest;
				OffAmazonPayments.Button("AmazonPayButton", '<?php echo $amazon['merchant_id']; ?>', {
					type: "PwA",
					authorization: function () {
						loginOptions = {scope: "profile postal_code payments:widget payments:shipping_address", popup: true};
						authRequest = amazon.Login.authorize(loginOptions, "<?php echo Utilities::generateUrl('amazon_pay', 'charge', array($order_id), CONF_WEBROOT_URL)?>");
					},
					onError: function (error) {
						console.log(error);
						amazon.Login.logout();
						document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
						window.location = '<?php echo Utilities::generateUrl('amazon_pay', 'charge', array($order_id), CONF_WEBROOT_URL)?>';
					}
				});
			</script>
			<?php 
		}
	}
?>