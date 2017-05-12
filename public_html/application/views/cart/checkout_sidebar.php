<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="your-cart">
            <div class="head-name"><?php echo Utilities::getLabel('L_Your_Cart')?> <span>(<label class="count_cart_items"><?php echo $cart_items?></label> <?php echo Utilities::getLabel('L_items')?>)</span></div>
            <div class="cart-wraper">
            <div class="bag-item">
              		<?php foreach ($products as $product) { ?>
         			<div class="item" id="quick-cart-row-<?php echo md5($product['key']); ?>">
		            <div class="pro-image"><img src="<?php echo Utilities::generateUrl('image','product_image',array($product["product_id"],'THUMB'))?>" alt="<?php echo $product["name"]?>"/></div>
        	    	<div class="product-desc">
            	    <div class="product-name"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>" ><strong><?php echo subStringByWords($product["name"],26)?></strong></a> <?php if (!$product['stock'] && Settings::getSetting("CONF_CHECK_STOCK")) { ?>
              		<span class="text-danger">***</span>
	              <?php } ?>
    	          <?php if ($product['option']) { ?>
        	      <?php foreach ($product['option'] as $option) { ?>
            	  <br />
	              - <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
    	          <?php } ?>
        	      <?php } ?>
            	  </div>
		              <input type="number" id="qty<?php echo md5($product['key']); ?>"  name="quantity[<?php echo md5($product['key']); ?>]" class="form-control qty" value="<?php echo $product['quantity']; ?>" min="<?php echo $product['minimum']?>" max="<?php echo $product['product_stock']?>" onchange="cart.short_update('<?php echo md5($product['key']); ?>','<?php echo md5($product['key']); ?>');">
        		      <div class="price"><?php echo Utilities::displayMoneyFormat($product['total'],true,true); ?></div>
		            </div>
        	  </div>
			<?php }?>
            </div>
            <?php if (count($products)>1) {?>
            <div class="controls"> <a class="prev"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <path fill-rule="evenodd" clip-rule="evenodd"  d="M0,35L40.8,0v70L0,35z"/>
                </svg></a> <a class="next"><svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M0,70L0,0l40.8,35L0,70z"/>
                </svg> </a> </div>
              <?php } ?>  
            </div>
          </div>
          <div class="coupons">
            <div class="discount-code"> <a href="#" class="btn tgl-triger"><?php echo Utilities::getLabel('M_Have_coupon')?></a>
              <div class="tgl-data coupon">
              	<span id="ajax_response"></span>
              	<form id="frmDiscountCoupon" name="frmDiscountCoupon" class="siteForm">
                      <input type="text" placeholder="<?php echo Utilities::getLabel('L_Enter_your_coupon')?>" name="coupon" value="<?php echo $cart_summary["cart_discounts"]["code"]?>" >
                      
                      <a href="#" id="RemoveDiscountCoupon" class="btn secondary-btn <?php if (empty($cart_summary["cart_discounts"]["code"])) {?>hide<?php } ?>"><?php echo Utilities::getLabel('M_Remove')?></a>
                      <input type="submit" value="<?php echo Utilities::getLabel('M_Apply')?>" class="primary-btn btn">
                    </form>
                    
                    <!---->
              </div>
              <?php if ($user_total_reward_points > 0) {?>
              <div class="gap"></div>
              <a href="#" class="btn tgl-triger"><?php echo Utilities::getLabel('L_Use_Reward_Points')?> (<?php echo Utilities::getLabel('L_Available')?> <?php echo $user_total_reward_points?>)</a>
              <div class="tgl-data reward">
              <?php
			 	 $max_cart_total = $cart_summary["cart_max_rewards_points"];
              ?>
              <p><?php echo Utilities::getLabel('L_Points_to_use')?> (<?php echo Utilities::getLabel('L_Max')?> <?php echo $max_cart_total?>) </label></p>
              <span id="ajax_response"></span>
                <form id="frmRewardPoints" name="frmRewardPoints" class="siteForm">
                  
                  <input type="text" name="reward_points" placeholder="<?php echo Utilities::getLabel('L_Enter_points_here')?>" 
                  value="<?php echo $cart_summary["reward_points"]>0?$cart_summary["reward_points"]:''?>" >
                  <input type="submit" value="<?php echo Utilities::getLabel('M_Apply')?>" class="btn primary-btn">
                  <a href="#" id="RemoveRewardPoints" class="btn secondary-btn <?php if (empty($cart_summary["reward_points"])) {?>hide<?php } ?>"><?php echo Utilities::getLabel('M_Remove')?></a>
                </form>
              </div>
              <?php } ?>
            </div>
          </div>
          <div class="order-summary" id="sidebar-payment-summary">
            
          </div>
          
<script type="text/javascript">
$(document).ready(function () {
	loadSideBarPaymentSummary();
	
	$('.prev').click(function(){
          $('.bag-item').slick('slickPrev');
        });
        
        $('.next').click(function(){
          $('.bag-item').slick('slickNext');
        });
});
</script>          