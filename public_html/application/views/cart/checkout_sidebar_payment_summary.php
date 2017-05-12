<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<h3><?php echo Utilities::getLabel('M_Payment_Summary')?></h3>
<ul>
  <li><span class="pull_left"><?php echo Utilities::getLabel('M_TOTAL_ITEMS')?> (<?php echo $cart_summary['items'];?>) </span><span class="pull_right"><strong><?php echo Utilities::displayMoneyFormat($cart_summary["cart_total"]); ?></strong> </span> </li>
  <?php if (!empty($cart_summary["cart_tax_total"])) {?>
  <li><span class="pull_left"><?php echo Utilities::getLabel('M_Tax')?> </span><span class="pull_right"><strong><?php echo Utilities::displayMoneyFormat($cart_summary["cart_tax_total"]); ?></strong> </span> </li>
  <?php }?>
  
  <?php if (!empty($cart_summary["cart_shipping_total"])) {?>
  <li><span class="pull_left"><?php echo Utilities::getLabel('M_Delivery_Charges')?> </span><span class="pull_right"><strong><?php echo Utilities::displayMoneyFormat($cart_summary["cart_shipping_total"]); ?></strong> </span> </li>
  <?php }?>
  
  <?php if (is_array($cart_summary["cart_discounts"]) && (!empty($cart_summary["cart_discounts"]))) {?>
  <li><span class="pull_left"><?php echo Utilities::getLabel('M_Coupon')?> (<strong><?php echo $cart_summary["cart_discounts"]["code"]?></strong>) </span><span class="pull_right"><strong><?php echo Utilities::displayMoneyFormat($cart_summary["cart_discounts"]["value"]); ?></strong> </span> </li>
  <?php } ?>
  
  <?php if (!empty($cart_summary["reward_points"])) {?>
  <li><span class="pull_left"><?php echo Utilities::getLabel('M_Reward_Points')?> (<strong><?php echo $cart_summary["reward_points"]?></strong>) </span><span class="pull_right"><strong><?php echo Utilities::displayMoneyFormat($cart_summary["reward_points"]); ?></strong> </span> </li>
  <?php } ?>
  
  <?php if (($user_balance > 0) && ($payment_ready)) {?>
  <li class="wallet"><span class="pull_left">
    <label class="checkbox">
      <input type="checkbox" <?php if ($cart_summary["cart_wallet_enabled"]==1) {?> checked="checked" <?php }?> name="pay_from_wallet" id="pay_from_wallet">
      <i class="input-helper"></i> <?php echo Utilities::getLabel('M_Use_My_Wallet_Credits')?></label>
    </span> <span class="pull_right"> <strong>- <?php echo Utilities::displayMoneyFormat($user_balance)?></strong></span></li>
  <?php } ?>  
  <li class="netpay"><span class="pull_left"><?php echo Utilities::getLabel('M_Net_Payable')?></span> <span class="pull_right"><?php echo Utilities::displayMoneyFormat($cart_summary["order_payment_gateway_charge"])?></span></li>
</ul>
<? if (($cart_summary["order_payment_gateway_charge"]<=0) && ($payment_ready)) {?>
<form id="frmWalletPayment" name="frmWalletPayment" class="siteForm" action="<?php echo Utilities::generateUrl('wallet_pay','send')?>">
<div class="place-btn"> 
	<div class="wallet-btn">
		<button class="btn primary-btn"><?php echo Utilities::getLabel('M_Pay_Now')?></button>
		<input type="button" class="btn secondary-btn" onclick="location.href = '<?php echo Utilities::generateUrl('cart','checkout')?>';"  value="<?php echo Utilities::getLabel('M_Cancel')?>">
	</div>
 	<div id="ajax_wallet_message"></div>   
</div>     
</form>
<? } ?>
