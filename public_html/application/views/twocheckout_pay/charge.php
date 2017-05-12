<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php 
if($payment_type == 'HOSTED'){  //check admin and application controller for confirmation
//hosted checkout
?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount); ?></strong> </p>
      <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"]); ?></strong> </p>
      
    </div>
    <div class="payment-from">
			<?php if (!isset($error)): ?>
                       <p><?=Utilities::getLabel('L_We_are_redirecting_payment_page')?>:</p>
			<?php echo  $frm->getFormHtml(); ?>
			<?php else: ?>
				  <div class="alert alert-danger"><?php echo $error; ?><div>
			<?php endif;?>
    </div>
  </div>
</div>
<script type="text/javascript">
    $(function(){
		setTimeout(function(){ $('form[name="frmTwoCheckout"]').submit() }, 5000);
	})
</script>	
<?php 
}else{ //Now we are calling Checkout via API
?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount); ?></strong> </p>
      <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"]); ?></strong> </p>
      
    </div>
    <div class="payment-from">
			<?php if (!isset($error)): ?>
			<?php echo  $frm->getFormHtml() ?>
			<?php else: ?>
				  <div class="alert alert-danger"><?php echo $error; ?><div>
			<?php endif;?>
			<div id="ajax_message"></div>
    </div>
  </div>
</div>	
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript" src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
<script>
var jq = $.noConflict();
var me = '';
var el = jq('#ajax_message');
var frm = '';
  // Called when token created successfully.
  var successCallback = function(cdata) {
    var myForm = document.getElementById('frmTwoCheckout');
    // Set the token as the value for the token input
    myForm.token.value = cdata.response.token.token;
    // IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
    //myForm.submit();
	
	var data = getFrmData(frm);
	data += '&outmode=json&is_ajax_request=yes';
	callAjax(me.attr('action'), data, function(response){
		var json = parseJsonData(response);
		me.data('requestRunning', false);
		if (json['error']) {
			el.html('<div class="alert alert-danger">'+json['error']+'<div>');
		}
		if (json['redirect']) {
			jq(location).attr("href",json['redirect']);
		}
	});
	
  };
  // Called when token creation fails.
  var errorCallback = function(data) {
    // Retry the token request if ajax call fails
    if (data.errorCode === 200) {
       // This error code indicates that the ajax call failed. We recommend that you retry the token request.
		tokenRequest();
    } else {	  
		me.data('requestRunning', false);
		el.html('<div class="alert alert-danger">'+data.errorMsg+'<div>');
	  
    }
  };
  var tokenRequest = function() {
    // Setup token request arguments
    var args = {
      sellerId: "<?php echo $sellerId; ?>",
      publishableKey: "<?php echo $publishableKey; ?>",
      ccNo: jq("#ccNo").val(),
      cvv: jq("#cvv").val(),
      expMonth: jq("#expMonth").val(),
      expYear: jq("#expYear").val()
    };
    // Make the token request
    TCO.requestToken(successCallback, errorCallback, args);
  };
  jq(function() {
    // Pull in the public encryption key for our environment
    TCO.loadPubKey("<?php echo $transaction_mode; ?>");
    jq("#frmTwoCheckout").submit(function(event) {
	    //some tweaks
	    event.preventDefault();
		me=jq(this);
		if ( me.data('requestRunning') ) {
			return;
		}
		frm=this;
		/* v = me.attr('validator');
		window[v].validate();
		if (!window[v].isValid()) return; */
		me.data('requestRunning', true);
		showHtmlElementLoading(el);
		
		// Call our token request function
		tokenRequest();
		// Prevent form from submitting
		return false;
    });
  });
</script>
<?php
}
?>