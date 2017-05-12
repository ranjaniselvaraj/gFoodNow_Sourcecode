                <!--<div class="space-lft-right"><?php echo Message::getHtml();?></div></br>-->
				<?php if ($products) { ?>
                <li>
                  <table class="table table-striped">
                    <tbody>
                	  <?php foreach ($products as $product) { ?>
                      <tr>
                        <td class="text-center"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($product["product_id"],'MINI'))?>" alt="<?php echo $product["name"]?>"/> </a></td>
                        <td class="text-left"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><?php echo $product["name"]?></a><?php if (!$product['stock'] && Settings::getSetting("CONF_CHECK_STOCK")) { ?>
                  			<span class="text-danger">***</span>
	                  <?php } ?>
    	              <?php if ($product['option']) { ?>
        		          <?php foreach ($product['option'] as $option) { ?>
                		 <br />
			                 - <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
            		      <?php } ?>
	                  <?php } ?><?php if ($product['shipping_free']) { ?>
                		 <br />
			                 - <small><?php echo Utilities::getLabel('L_Free_Shipping')?></small>
	                  <?php } ?></td>
                        <td class="text-right" nowrap="nowrap">x <?php echo $product['quantity']; ?></td>
                        <td class="text-right" nowrap="nowrap"><?php echo Utilities::displayMoneyFormat($product['total'],true,true); ?></td>
                        <td class="text-center"><a href="javascript:void(0);" onclick="cart.remove('<?php echo md5($product['key']); ?>');" class="actions"><img src="<?php echo CONF_WEBROOT_URL?>images/action-icn-delete.png" width="16" height="17" alt="<?php echo Utilities::getLabel('L_Remove')?>"/></a></td>
                      </tr>
                      <?php } ?>
                      
                    </tbody>
                  </table>
                </li>
                <li>
                  <div>
                    <table class="table table-bordered">
                      <tbody>
                        <tr>
                          <td class="text-right"><?php echo Utilities::getLabel('M_Cart_Total')?></td>
                          <td class="text-right"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_total"]); ?></td>
                        </tr>
                        <?php if (($cart_summary["cart_tax_total"]>0)){?>
                        <tr>
                          <td class="text-right"><?php echo Utilities::getLabel('M_Tax')?></td>
                          <td class="text-right"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_tax_total"]); ?></td>
                        </tr>
                        <?php } ?>
						<?php if (($cart_summary["cart_shipping_total"]>0) || (is_array($cart_summary["cart_discounts"])) ){?>
                        <tr>
                          <td class="text-right"><?php echo Utilities::getLabel('M_Shipping')?></td>
                          <td class="text-right"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_shipping_total"]); ?></td>
                        </tr>
                        <?php } ?>
                        <?php if (is_array($cart_summary["cart_discounts"]) && (!empty($cart_summary["cart_discounts"]))) {?>
                        <tr>
                          <td class="text-right"><?php echo Utilities::getLabel('M_Coupon')?> (<strong><?php echo $cart_summary["cart_discounts"]["code"]?></strong>)</td>
                          <td class="text-right"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_discounts"]["value"]); ?></td>
                        </tr>
                        <?php } ?>
                         <?php if (!empty($cart_summary["reward_points"])) {?>
                         <tr>
                          <td class="text-right"><?php echo Utilities::getLabel('M_Reward_Points')?> (<strong><?php echo $cart_summary["reward_points"]?></strong>)</td>
                          <td class="text-right"><?php echo Utilities::displayMoneyFormat($cart_summary["reward_points"]); ?></td>
                        </tr>
	                      <?php } ?>
                        <tr>
                          <td class="text-right"><strong><?php echo Utilities::getLabel('M_Total')?></strong></td>
                          <td class="text-right"><?php echo Utilities::displayMoneyFormat($cart_summary["net_total_after_discount"]); ?></td>
                        </tr>
                      </tbody>
                    </table>
                    
                    
                    <div class="text-right button-aligned"><a class="btn secondary-btn" href="<?php echo Utilities::generateUrl('cart')?>"> <i class="cart-icn"> <svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="70px" width="70px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                          <path d="M70.1,11.7h-7l-7,21H25.7l-7-23.3c0,0-2.8-9.2-7-9.3h-7c0,0-5.5,1.9-4.7,4.7c0,0,0.3,6.7,9.3,2.3l11.7,35
	h39.6L70.1,11.7z" fill="#ffffff"/>
                          <path d="M29.2,46.3c3.2,0,5.8,2.6,5.8,5.8s-2.6,5.8-5.8,5.8s-5.8-2.6-5.8-5.8C23.4,49,26,46.3,29.2,46.3z" fill="#ffffff"/>
                          <path d="M52.4,46.3c3.2,0,5.8,2.6,5.8,5.8s-2.6,5.8-5.8,5.8s-5.8-2.6-5.8-5.8C46.5,49,49.2,46.3,52.4,46.3z" fill="#ffffff"/>
                          <rect height="7" width="28" fill="#ffffff" y="10.5" x="24.4"/>
                          <rect height="28" width="7" fill="#ffffff" y="0" x="34.9"/>
                          </svg></i><?php echo Utilities::getLabel('M_View_Cart')?></a> <a class="btn primary-btn" href="<?php echo Utilities::generateUrl('cart','checkout')?>"><?php echo Utilities::getLabel('M_Checkout')?></a></div>
                     
                  </div>
                </li>
                <?php }else{ ?>
					   <div class="alert alert-warning"><?php echo Utilities::getLabel('M_Your_cart_is_empty')?></div>
				<?php }?>
