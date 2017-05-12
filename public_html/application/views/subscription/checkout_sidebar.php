<?php defined('SYSTEM_INIT') or die('Invalid Usage');?>
<div class="your-cart">
	<div class="head-name"><?=Utilities::getLabel('L_Your_Subscription_Cart')?> <span>(<label class="count_cart_items">1</label> <?=Utilities::getLabel('L_item')?>)</span></div>
  </div>
  <?php if ($cart_summary['cart_total']>0){?>
  <div class="coupons">
	<div class="discount-code"> <a href="#" class="btn tgl-triger"><?=Utilities::getLabel('M_Apply_Coupon')?></a>
	  
      
      <div class="tgl-data coupon">
              	<span id="ajax_response"></span>
              	<form id="frmSubscriptionDiscountCoupon" name="frmSubscriptionDiscountCoupon" class="siteForm">
                      <input type="text" placeholder="<?php echo Utilities::getLabel('L_Enter_your_coupon_here')?>" name="coupon" value="<?php echo $cart_summary["cart_discounts"]["code"]?>" >
                      <input type="submit" value="<?php echo Utilities::getLabel('M_Apply')?>" class="primary-btn btn">
                      <a href="#" id="RemoveDiscountCoupon" class="btn secondary-btn <?php if (empty($cart_summary["cart_discounts"]["code"])) {?>hide<?php } ?>"><?php echo Utilities::getLabel('M_Remove')?></a>
                    </form>
              </div>
	</div>
  </div>
  <?php } ?>	
	<div class="order-summary" id="sidebar-payment-summary"></div>
<script type="text/javascript">
$(document).ready(function () {
	loadSideBarPaymentSummary();
});
</script>          