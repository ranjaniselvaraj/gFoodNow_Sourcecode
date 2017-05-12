<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="cart-tbl">
                <thead>
                  <tr>
                    <th><?php echo Utilities::getLabel('M_Product_Name')?> </th>
                    <th><?php echo Utilities::getLabel('M_Price')?></th>
                    <th><?php echo Utilities::getLabel('M_QTY')?></th>
                    <th nowrap="nowrap"><?php echo Utilities::getLabel('M_Delivery_Details')?></th>
                    <th><?php echo Utilities::getLabel('M_SubTotal')?></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product) { $sn++; ?>	
                  <tr id="cart-row-<?php echo md5($product['key']); ?>">
                    <td><span class="mobile-caption"><?php echo Utilities::getLabel('M_Product_Name')?></span><div class="product-name"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><?php echo subStringByWords($product["name"],50)?></a>
                  <?php if ($product['option']) { ?>
                  <?php foreach ($product['option'] as $option) { ?>
                  <br />
                  - <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                  <?php } ?>
                  <?php } ?><?php if ($product['shipping_free']) { ?>
                		 <br />
			                 - <small><?php echo Utilities::getLabel('L_Free_Shipping')?></small>
	                  <?php } ?>
                  </div></td>
                    
                    <td nowrap="nowrap"><div class="price"><span class="mobile-caption"><?php echo Utilities::getLabel('M_PRICE')?></span><?php echo Utilities::displayMoneyFormat($product['price'],true,true); ?></div></td>
                    <td nowrap="nowrap"><span class="mobile-caption"><?php echo Utilities::getLabel('M_QTY')?></span><?php echo ($product['quantity']); ?></div></td>
                    <td nowrap="nowrap"><span class="mobile-caption"><?php echo Utilities::getLabel('M_Delivery_Details')?></span>
                    
                    <?php if ($product['shipping']) { ?>
                    <div class="shipping">
                     <?php if (!empty($product['selected_shipping_option']['scompany_name'])):?>
                     <?php echo Utilities::getLabel('M_Delivered_In')?> <?php echo $product['selected_shipping_option']['sduration_label']?> <?php echo Utilities::getLabel('M_by')?> <i><?php echo $product['selected_shipping_option']['scompany_name']?></i>
                     <?php else: ?>
                     <?php echo Utilities::getLabel('M_Delivered_By')?> <em><?php echo $product['selected_shipping_option']['sduration_label']?></em>                     <?php endif;?>
                     <br/>
                    <small>
                    - <?php echo Utilities::getLabel('M_Shipping_Cost')?>: <?php echo $product["shipping_free"]==0?Utilities::displayMoneyFormat($product['selected_shipping_option']['pship_charges']):Utilities::getLabel("M_Free")?><br/>
					<?php if (!empty($product['selected_shipping_option']['pship_additional_charges'])):?>
                    - <?php echo Utilities::getLabel('M_With_another_item')?>: <?php echo $product["shipping_free"]==0?Utilities::displayMoneyFormat($product['selected_shipping_option']['pship_additional_charges']):Utilities::getLabel("M_Free")?>
					</small>
                    <?php endif;?> </div>
                    <?php } else {?><div class="shipping"><?php echo Utilities::getLabel('M_Delivery_details_not_applicable')?></div><?php } ?></td>
                    
                    </td>
                    <td nowrap="nowrap"><div class="sub-total"><span class="mobile-caption"><?php echo Utilities::getLabel('M_SUBTOTAL')?></span> <?php echo Utilities::displayMoneyFormat($product['net_total_wth_tax'])?></div></td>
                    <td><a href="javascript:void(0);" onclick="cart.short_remove('<?php echo md5($product['key']); ?>');" class="actions"><img src="<?php echo CONF_WEBROOT_URL?>images/action-icn-delete.png" width="16" height="17" alt="<?php echo Utilities::getLabel('L_Remove')?>"/></a></td>
                  </tr>
                  <?php }?> 
                </tbody>
              </table>
              <div class="total-bottom clearfix">
            	<div class="total-side">
    	          <div class="btn-row"> <a href="javascript:void(0);" id="cartContinue" class="btn primary-btn"> <?php echo Utilities::getLabel('M_Continue')?> </a></div>
               </div>
          	 </div>
              