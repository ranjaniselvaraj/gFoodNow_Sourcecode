<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php include CONF_THEME_PATH . 'print_header.php'; ?>
	<div class="gap"></div>
     
	<div class="space-lft-right">
    		<div class="box-head">
           	<h3><?php echo Utilities::getLabel('L_Order_Details')?> - #<?php echo $order_detail['invoice_number']?></h3>
		    </div>
            <div class="gap"></div>
            <table class=" tbl-normal">
              <tbody>
                <tr>
                 <td width="10%" >
                 	 <?php if ($child_order || $order_detail["primary_order"]!=1){ ?>	
                  	 <img src="<?php echo Utilities::generateUrl('image','shop_logo',array($order_detail["shop_logo"],'THUMB'))?>" width="97" height="97" alt="<?php echo $shop["shop_name"]?>"/>
                     <?php } else {?>
                     <img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>"  alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/>
                     <?php } ?>
                     
        	     </td>
                 <td width="40%" ><?php if ($child_order || $order_detail["primary_order"]!=1){ 
					 		echo '<strong>'.$order_detail["shop_name"].'</strong><br/>'.((strlen($order_detail['shop_contact_person']) > 0)?$order_detail['shop_contact_person']:'').((strlen($order_detail['shop_address_line_1']) > 0)?'<br/>'.$order_detail['shop_address_line_1']:'') .((strlen($order_detail['shop_address_line_2']) > 0)?'<br/>'.$order_detail['shop_address_line_2']:'') . ((strlen($order_detail['shop_city']) > 0)?'<br/>'.$order_detail['shop_city'] . ', ':'') . $order_detail['shop_postcode']; ?><br><?php echo $order_detail['shop_state_name']; ?>, <?php echo $order_detail['shop_country_name'].((strlen($order_detail['shop_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['shop_phone']:''); 
						 	}else{
								 echo '<strong>'.Settings::getSetting("CONF_WEBSITE_NAME").'</strong><br/>'.(nl2br(Settings::getSetting("CONF_ADDRESS"))) ?><?php echo ((strlen(Settings::getSetting("CONF_SITE_PHONE")) > 0)?'<br/><strong>T</strong>:'.Settings::getSetting("CONF_SITE_PHONE"):'').((strlen(Settings::getSetting("CONF_SITE_FAX")) > 0)?'<br/><strong>F</strong>:'.Settings::getSetting("CONF_SITE_FAX"):''); 
							}
				  ?></td>
                 <td width="50%">
					 
                    <b><?php echo Utilities::getLabel('L_Date_Added') ?>:</b> <?php echo Utilities::formatDate($order_detail["order_date_added"]) ?><br />
                    <b><?php echo Utilities::getLabel('L_Invoice_Number')?>:</b> <?php echo $order_detail['opr_order_invoice_number']; ?><br />
				    <b><?php echo Utilities::getLabel('L_Payment_Method') ?>:</b> <?php echo Utilities::displayNotApplicable($order_detail['payment_methods']); ?><br />
					
        	     </td>
                </tr>
              </tbody>
            </table>
      <div class="gap"></div>
            <table class=" tbl-normal">
              <tbody>
                <tr>
                  <th width="50%"><?php echo Utilities::getLabel('L_Billing_Details')?>  </th>
                  <?php if ($order_detail['opr_shipping_required'] || $order_detail["primary_order"]==1) {?>
                  <th width="50%"><?php echo Utilities::getLabel('L_Shipping_Details')?></th>
				  <?php }?>
                </tr>
                <tr>
                  <td valign="top">
			 <?php echo '<strong>'.$order_detail["order_billing_name"].'</strong><br/>'.((strlen($order_detail['order_billing_address1']) > 0)?$order_detail['order_billing_address1']:'') .((strlen($order_detail['order_billing_address2']) > 0)?'<br/>'.$order_detail['order_billing_address2']:'') . ((strlen($order_detail['order_billing_city']) > 0)?'<br/>'.$order_detail['order_billing_city'] . ', ':'') . $order_detail['order_billing_postcode']; ?><br><?php echo $order_detail['order_billing_state']; ?>, <?php echo $order_detail['order_billing_country'].((strlen($order_detail['order_billing_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['order_billing_phone']:'').((strlen($order_detail['order_billing_fax']) > 0)?'<br/><strong>F</strong>:'.$order_detail['order_billing_fax']:''); ?>
             </td>
                 <?php if ($order_detail['opr_shipping_required'] || $order_detail["primary_order"]==1) {?> <td valign="top"><?php echo '<strong>'.$order_detail["order_shipping_name"].'</strong><br/>'.((strlen($order_detail['order_shipping_address1']) > 0)?$order_detail['order_shipping_address1']:'') .((strlen($order_detail['order_shipping_address2']) > 0)?'<br/>'.$order_detail['order_shipping_address2']:'') . ((strlen($order_detail['order_shipping_city']) > 0)?'<br/>'.$order_detail['order_shipping_city'] . ', ':'') . $order_detail['order_shipping_postcode']; ?><br><?php echo $order_detail['order_shipping_state']; ?>, <?php echo $order_detail['order_shipping_country'].((strlen($order_detail['order_shipping_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['order_shipping_phone']:'').((strlen($order_detail['order_shipping_fax']) > 0)?'<br/><strong>F</strong>:'.$order_detail['order_shipping_fax']:''); ?></td> <?php } ?>
                </tr>
              </tbody>
            </table>
            <div class="gap"></div>
            <table class=" tbl-normal">
              <tbody>
                <tr>
                    <th width="50%"><?php echo Utilities::getLabel('L_Product')?></th>
                    <th width="18%"><?php echo Utilities::getLabel('L_Qty')?></th>
                    <th width="18%"><?php echo Utilities::getLabel('L_Unit_Price')?></th>
                    <th class="text-right"><?php echo Utilities::getLabel('L_Subtotal')?></th>
                </tr>
                <?php	foreach($order_detail["products"] as $sn=>$row){
									$opr_customer_buying_price=$row["opr_customer_buying_price"];
									$customization_price=$row["opr_customer_customization_price"];
									$adds=" (+".$currencyObj->format($customization_price,$order_detail["order_currency_code"],$order_detail["order_currency_value"]).")";  
									$options=Utilities::renderHtml($row["opr_customization_string"]);
								    $opr_customer_buying_price+=$customization_price;
								    $ind_price_total=$row["opr_qty"]*$opr_customer_buying_price;
									$ind_price_total_tax=$ind_price_total;
									$cart_total+=$ind_price_total; 
									$shipping_total+=$row["opr_shipping_charges"];
									$customization_cost = (count($row['order_options'])>0)?"<br/><strong>".Utilities::getLabel('L_COMBINATION_SELECTED')."</strong>":"";
				?>
                <tr>
                 <td class="titletd"><span class="cellcaption"><?php echo Utilities::getLabel('L_Product')?></span> 
                             
                  <span class="item_Title"><?php echo $row["opr_name"]?><?php echo  $customization_cost;?></span>					<?php foreach ($row['order_options'] as $option) { ?>
               					 <br />
					                <?php if ($option['type'] != 'file') { ?>
					                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
				                	<?php } else { ?>
		           						&nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
	        				       	 <?php } ?>
				                <?php } ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Qty')?></span><?php echo $row["opr_qty"]?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Unit_Price')?></span><?php echo $currencyObj->format($row["opr_customer_buying_price"] + $row["opr_customer_customization_price"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Subtotal')?></span><?php echo $currencyObj->format( $row['opr_qty']*($row["opr_customer_buying_price"] + $row["opr_customer_customization_price"]),$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php } ?>
				<?php
					if ($order_detail["primary_order"]==0){
						$tax=$order_detail["opr_tax"];
						$discount=0;
					}else{
	    	            $tax=$order_detail["order_tax_charged"];
						$discount=$order_detail["order_discount_total"];
						$reward_points=$order_detail["order_reward_points"];
					}
                ?>
                <tr>
                  <td class="mergedcell" colspan="3"><?php echo Utilities::getLabel('L_Cart_Total')?></td>
                  <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Subtotal')?></span><?php echo $currencyObj->format($cart_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?> </td>
                </tr>
                <?php if ($tax>0):?> 
                <tr>
               		<td colspan="3" class="mergedcell"><?php echo Utilities::getLabel('L_VAT')?></td>
	                <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_VAT')?></span>+ <?php echo $currencyObj->format($tax,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php endif;?> 
                  <tr>
                    <td colspan="3" class="mergedcell"><?php echo Utilities::getLabel('L_Shipping_Handling')?> </td>
                    <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Shipping_Handling')?></span>+ <?php echo $currencyObj->format($shipping_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  </tr>
				               
				<?php if ($discount>0):?>
                <tr>
                    <td colspan="3" class="mergedcell"><?php echo Utilities::getLabel('L_Coupon')?> (<?php echo $order_detail["order_discount_coupon"]?>)</td>
                    <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Coupon')?> (<?php echo $order_detail["order_discount_coupon"]?>)</span>- <?php echo $currencyObj->format($order_detail["order_discount_total"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php endif;?>
                <?php if ($reward_points>0):?>
                <tr>
                    <td colspan="3" class="mergedcell"><?php echo Utilities::getLabel('L_Reward_Points')?> (<?php echo $order_detail["order_reward_points"]?>)</td>
                    <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Reward_Points')?> (<?php echo $order_detail["order_reward_points"]?>)</span>- <?php echo $currencyObj->format($order_detail["order_reward_points"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php endif;?>
                <?php $grand_total=$cart_total+$shipping_total+$tax-$discount-$reward_points; ?>
                <tr class="trLast">
                	<td colspan="3" class="mergedcell"><?php echo Utilities::getLabel('L_Grand_Total')?></td>
	                <td class="text-right"><?php echo $currencyObj->format($grand_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
              	</tr>
	           <?php if (($order_detail["order_credits_charged"]>0) && ($order_detail["primary_order"]==0)){?>
               <tr>
                <td colspan="5" class="mergedcell">(<?php echo Utilities::getLabel('L_Credits_Used')?>: <?php echo $currencyObj->format($order_detail["order_credits_charged"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?> + <?php echo Utilities::getLabel('L_Paid_Amount')?> <?php echo $currencyObj->format($order_detail["order_actual_paid"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?>)</td>
              </tr>
              <?php }?> 
              <?php if ($order_detail["opr_refund_qty"]>0):?>
              <tr>
                <td colspan="3" class="mergedcell"><?php echo Utilities::getLabel('L_Refund_Amount_Qty')?>. [<?php echo $order_detail["opr_refund_qty"]?>]</td>
                <td class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Refund_Amount_Qty')?>. [<?php echo $order_detail["opr_refund_qty"]?>]</span> <?php echo $currencyObj->format($order_detail["opr_total_refund_amount"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?> </td>
              </tr>
              <?php endif;?>
              </tbody>
            </table>
           
       </div>
