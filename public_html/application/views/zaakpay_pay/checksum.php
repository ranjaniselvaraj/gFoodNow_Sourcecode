<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="payment-from">
         <p>Do Not Refresh or Press Back ... Redirecting to Zaakpay</p>
         <form action="https://api.zaakpay.com/transact" method="post">
				<?php
					Checksum::outputForm($checksum);
				?>
			</form>
    </div>
  </div>
</div>
<script type="text/javascript">
 	$(function(){
		var form = document.forms[0];
		form.submit();
	})
   
</script>	
