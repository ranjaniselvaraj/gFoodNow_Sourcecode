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
			
			if(intval($order_id) > 0 && $order_info["order_payment_status"] == 0){
				?>
				 <div class="text-center" style="margin-top:40px;">
					<div id="addressBookWidgetDiv" style="width:400px; height:240px; display:inline-block;"></div>
					<div id="walletWidgetDiv" style="width:400px; height:240px; display:inline-block;"></div>
				</div>
                
					<div class="amazon-submit-wrap">
						<a href="javascript:void(0);" class="amazon-submit btn primary-btn">Please wait...</a>
						<p>Or</p>
						<a href="javascript:void(0);" onclick="logout();" class="btn secondary-btn">Reset</a>
					</div>
                
				<?php 
			}
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
				var redirectAfterSuccess = '<?php echo Utilities::generateUrl('custom', 'payment_success', array(), CONF_WEBROOT_URL); ?>';
				var orderId  = '<?php echo $order_id; ?>';
				window.onAmazonLoginReady = function () {
					amazon.Login.setClientId('<?php echo $amazon['client_id']; ?>');
					amazon.Login.setUseCookie(true);
				};
			</script>
			<script type='text/javascript' src='https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js'></script>
			<script type="text/javascript">
				function logout(){
					amazon.Login.logout();
					document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
					window.location = '<?php echo Utilities::generateUrl('amazon_pay', 'charge', array($order_id), CONF_WEBROOT_URL)?>';
				}
				
				var orderRefId = false;
				 new OffAmazonPayments.Widgets.AddressBook({
					sellerId: "<?php echo $amazon['merchant_id']; ?>",
					onOrderReferenceCreate: function (orderReference) {
					   var access_token = "";
						$.post("<?php echo Utilities::generateUrl('amazon_pay', 'get_details', array($order_id), CONF_WEBROOT_URL)?>", {
							orderReferenceId: orderReference.getAmazonOrderReferenceId(),
							addressConsentToken: access_token,
						}).done(function (data) {
							try{
								
								var jsonObj = $.parseJSON(data);
								if(jsonObj.hasOwnProperty('status') && jsonObj.hasOwnProperty('msg')){
									if(jsonObj.status == 0 && jsonObj.msg != ''){
										
										var jsonNewObj = $.parseJSON(jsonObj.msg);
										if(jsonNewObj.hasOwnProperty('Error')){
											
											if(jsonNewObj.Error.hasOwnProperty('Message')){
												
												if($('.payment-from').find('.error-wrap.error').length > 0){
													$('.payment-from .error-wrap.error').html('<p>'+jsonNewObj.Error.Message+'</p>');
												}else{
													$('.payment-from').prepend('<div class="error-wrap error"><p>'+jsonNewObj.Error.Message+'</p></div>');
												}
												
											}
											
										}
										
										logout();
										
									}else if(jsonObj.status == 1 && jsonObj.msg != ''){
										
										var jsonNewObj = $.parseJSON(jsonObj.msg);
										if(jsonNewObj.ResponseStatus == 200){
											
											orderRefId = jsonNewObj.GetOrderReferenceDetailsResult.OrderReferenceDetails.AmazonOrderReferenceId;
											
											if($('.payment-from').find('.amazon-submit-wrap').length  > 0){
												if($('.payment-from .amazon-submit-wrap').find('.amazon-submit').length  > 0){
													$('.payment-from .amazon-submit-wrap .amazon-submit').text('Confirm Payment');
												}
											}
											
										}
										
									}
								}else{
									console.log(data);
									logout();
								}
							} catch(e) {
								console.log(e.message);
								console.log(data);
								logout();
							}
						});
						
					},
					onAddressSelect: function (orderReference) {
						
					},
					design: {
						designMode: 'responsive'
					},
					onError: function (error) {
						console.log(error);
						logout();
					}
				}).bind("addressBookWidgetDiv");
				new OffAmazonPayments.Widgets.Wallet({
					sellerId: "<?php echo $amazon['merchant_id']; ?>",
					onPaymentSelect: function (orderReference) {
					},
					design: {
						designMode: 'responsive'
					},
					onError: function (error) {
						console.log(error);
						logout();
					}
				}).bind("walletWidgetDiv");
			</script>
			<?php 
		}
	}
?>