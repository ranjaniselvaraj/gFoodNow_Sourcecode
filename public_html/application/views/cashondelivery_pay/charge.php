<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-page">
  <div class="cc-payment">
    <div class="logo-payment"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"), 'THUMB'), CONF_WEBROOT_URL)?>" alt=""/></div>
    <div class="reff total-pay">
      <p class="fl"><?=Utilities::getLabel('L_Payable_Amount')?>: <strong><?php echo Utilities::displayMoneyFormat($payment_amount)?></strong> </p>
      <p class="fr"><?=Utilities::getLabel('L_Order_Invoice')?>: <strong><?php echo Utilities::displayNotApplicable($order_info["invoice"])?></strong> </p>
      
    </div>
    <div class="payment-from">
			<? if (!isset($error)): ?>
				<?php echo  $frm->getFormHtml() ?>
			<? else: ?>
				  <div class="alert alert-danger"><?php echo $error?><div>
			<? endif;?>
            <div id="ajax_message"></div>
    </div>
  </div>
</div>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="text/javascript">
    $(function(){
        $('form[name="frmPaymentForm"]').bind('submit', function(event){
				event.preventDefault();
				var me=$(this);
				if ( me.data('requestRunning') ) {
					return;
				}
				var frm=this;
				el = $('#ajax_message');
				v = me.attr('validator');
				window[v].validate();
				if (!window[v].isValid()) return;
				me.data('requestRunning', true);
				showHtmlElementLoading(el);
				var data = getFrmData(frm);
				data += '&outmode=json&is_ajax_request=yes';
				callAjax(me.attr('action'), data, function(response){
					var json = parseJsonData(response);
					me.data('requestRunning', false);
					if (json['error']) {
						el.html('<div class="alert alert-danger">'+json['error']+'<div>');
					}
					if (json['redirect']) {
						$(location).attr("href",json['redirect']);
					}
				});
						
				
		
		return false;					
      });
    })
</script>