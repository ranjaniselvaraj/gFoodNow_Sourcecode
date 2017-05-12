<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
	<div class="fixed-container">
		<div class="dashboard">
			<h3>De-Activate Auto-Renewal Confirmation</h3>
			<div class="gap"></div>
			<div>IMPORTANT NOTE: If you de-activate Auto-Renewal - you will not be charged automatically and you will need to go through the payment process every time you make payment. It will Cancel your current Subscription.  </div>
			<div class="gap"></div>
			<div><a class="btn" href="<?php echo Utilities::generateUrl('account','cancel_subscription',array($order_info['mporder_id'] , 0))?>" onClick="return cancelSubscription(this);" >I wish to deactivate auto-renewal</a> <br/><br/> <a href="javascript:void(0)" class="btn" onClick="closePreDeactivateAutoRenewalBox();">I do not want to deactivate auto-renewal</a></div>
		</div>
	</div>
</div>