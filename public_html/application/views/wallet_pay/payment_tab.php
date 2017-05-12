<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="payment-table-cell">
  <p><strong><?php echo sprintf(Utilities::getLabel('M_Pay_using_Payment_Method'),$payment_method["ppcpmethod_name"])?>:</strong></p><br/>
  <p><?php echo $payment_method["ppcpmethod_details"]?></p><br/>
  <?php if (!isset($error)): ?>
	  <?php echo  str_replace("<br>", " ",$frm->getFormHtml()); ?>
  <?php else: ?>
  <div class="alert alert-danger"><?php echo $error?><div>
  <?php endif;?>
  <div id="ajax_message"></div>
</div>
<script type="text/javascript">
	$(function(){
        $('form[name="frmPaymentTabForm"]').bind('submit', function(event){
				event.preventDefault();
				var me=$(this);
				if ( me.data('requestRunning') ) {
					return;
				}
				var frm=this;
				var href=generateUrl('common', 'check_ajax_user_logged_in');
					$.ajax({url: href,async: false}).done(function(html) {
							if (html==false){
								login_popupbox();
							}else{
								el = $('#ajax_message');
								me.data('requestRunning', true);
								showHtmlElementLoading(el);
						        var data = getFrmData(frm);
						        data += '&outmode=json&is_ajax_request=yes';
						        callAjax(generateUrl('wallet_pay','confirm_recharge'), data, function(response){
									var json = parseJsonData(response);
									me.data('requestRunning', false);
									if (json['error']) {
										el.html('<div class="alert alert-danger">'+json['error']+'<div>');
									}
									if (json['success']) {
										$(location).attr("href",me.attr('action'));
									}
			
								});
						}
					});
		
		return false;					
      });
    })
</script>
