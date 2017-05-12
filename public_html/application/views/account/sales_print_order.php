<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php include CONF_THEME_PATH . 'print_header.php'; ?>
	<div class="gap"></div>
     
	<div class="space-lft-right">
    		<div class="box-head">
           	<h3><?php echo Utilities::getLabel('L_Order_Details')?> - #<?php echo $order_detail['opr_order_invoice_number']?></h3>
		    </div>
            <div class="gap"></div>
            <table class=" tbl-normal">
              <tbody>
                <tr>
                 <td width="10%" >
                  	 <img src="<?php echo Utilities::generateUrl('image','shop_logo',array($order_detail["shop_logo"],'THUMB'))?>" width="97" height="97" alt="<?php echo $shop["shop_name"]?>"/>
        	     </td>
                 <td width="40%" ><?php echo '<strong>'.$order_detail["shop_name"].'</strong><br/>'.((strlen($order_detail['shop_contact_person']) > 0)?$order_detail['shop_contact_person']:'').((strlen($order_detail['shop_address_line_1']) > 0)?'<br/>'.$order_detail['shop_address_line_1']:'') .((strlen($order_detail['shop_address_line_2']) > 0)?'<br/>'.$order_detail['shop_address_line_2']:'') . ((strlen($order_detail['shop_city']) > 0)?'<br/>'.$order_detail['shop_city'] . ', ':'') . $order_detail['shop_postcode']; ?><br><?php echo $order_detail['shop_state_name']; ?>, <?php echo $order_detail['shop_country_name'].((strlen($order_detail['shop_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['shop_phone']:''); ?></td>
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
                  <?php if ($order_detail['opr_shipping_required']) {?>
                  <th width="50%"><?php echo Utilities::getLabel('L_Shipping_Details')?></th>
				  <?php }?>
                </tr>
                <tr>
                  <td valign="top">
			 <?php echo '<strong>'.$order_detail["order_billing_name"].'</strong><br/>'.((strlen($order_detail['order_billing_address1']) > 0)?$order_detail['order_billing_address1']:'') .((strlen($order_detail['order_billing_address2']) > 0)?'<br/>'.$order_detail['order_billing_address2']:'') . ((strlen($order_detail['order_billing_city']) > 0)?'<br/>'.$order_detail['order_billing_city'] . ', ':'') . $order_detail['order_billing_postcode']; ?><br><?php echo $order_detail['order_billing_state']; ?>, <?php echo $order_detail['order_billing_country'].((strlen($order_detail['order_billing_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['order_billing_phone']:'').((strlen($order_detail['order_billing_fax']) > 0)?'<br/><strong>F</strong>:'.$order_detail['order_billing_fax']:''); ?>
             </td>
                 <?php if ($order_detail['opr_shipping_required']) {?> <td valign="top"><?php echo '<strong>'.$order_detail["order_shipping_name"].'</strong><br/>'.((strlen($order_detail['order_shipping_address1']) > 0)?$order_detail['order_shipping_address1']:'') .((strlen($order_detail['order_shipping_address2']) > 0)?'<br/>'.$order_detail['order_shipping_address2']:'') . ((strlen($order_detail['order_shipping_city']) > 0)?'<br/>'.$order_detail['order_shipping_city'] . ', ':'') . $order_detail['order_shipping_postcode']; ?><br><?php echo $order_detail['order_shipping_state']; ?>, <?php echo $order_detail['order_shipping_country'].((strlen($order_detail['order_shipping_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['order_shipping_phone']:'').((strlen($order_detail['order_shipping_fax']) > 0)?'<br/><strong>F</strong>:'.$order_detail['order_shipping_fax']:''); ?></td> <?php } ?>
                </tr>
              </tbody>
            </table>
            <div class="gap"></div>
            <table class="tbl-normal">
              <tbody>
                <tr>
                  <th># </th>
                  <th><?php echo Utilities::getLabel('L_Product_Name')?></th>
                  <th><?php echo Utilities::getLabel('L_Shipping')?></th>
                  <th><?php echo Utilities::getLabel('L_Price')?> </th>
                  <th><?php echo Utilities::getLabel('L_Qty')?></th>
                  <th class="text-right"><?php echo Utilities::getLabel('L_Total')?></th>
                </tr>
                <?php 	
				  $opr_customer_buying_price=$order_detail["opr_customer_buying_price"];
				  $customization_price=$order_detail["opr_customer_customization_price"];
				  $adds=" (+".$currencyObj->format($order_detail["opr_customization_price"],$order_detail["order_currency_code"],$order_detail["order_currency_value"]).")";
				  $sku_codes=$oval['opr_code']!=""?$order_detail['opr_code']:$order_detail['opr_sku'];
				  $options=Utilities::renderHtml($order_detail["opr_customization_string"]);
				  $opr_customer_buying_price+=$customization_price;
				  $ind_price_total=$order_detail["opr_qty"]*$opr_customer_buying_price;
				  
				  $customization_cost = (count($order_detail['order_options'])>0)?"<br/><strong>".Utilities::getLabel('L_COMBINATION_SELECTED')."</strong>$adds":"";
				?>
                <tr>
                  <td><span class="cellcaption">#</span>1</td>
				  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Product_Name')?></span><?php echo $order_detail["opr_name"]?><?php echo $customization_cost?>
                  
                  <?php foreach ($order_detail['order_options'] as $option) { ?>
               					 <br />
					                <?php if ($option['type'] != 'file') { ?>
					                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
				                	<?php } else { ?>
		           						&nbsp;<small> - <?php echo $option['name']; ?>: <a href="<?php echo $option['href']; ?>"><?php echo $option['value']; ?></a></small>
	        				       	 <?php } ?>
				                <?php } ?>
                                
                  <?php if (!empty($sku_codes)):?><br/><strong><?php echo Utilities::getLabel('L_SKU')?></strong>: <?php echo $sku_codes?><?php endif; ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Shipping')?></span><?php echo $order_detail["opr_shipping_required"]?$order_detail["opr_shipping_label"]:'-NA-';?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Listed_Price')?> </span><?php echo $currencyObj->format($order_detail["opr_customer_buying_price"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Qty')?></span><?php echo $order_detail["opr_qty"]?></td>
                  <td nowrap="nowrap" class="text-right"><span class="cellcaption"><?php echo Utilities::getLabel('L_Total')?></span><?php echo $currencyObj->format($ind_price_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php 
					$cart_total_without_tax=($order_detail["opr_customer_buying_price"]+$order_detail["opr_customer_customization_price"])*$order_detail["opr_qty"]; 
					$vat=$cart_total_with_tax-$cart_total_without_tax;
				?>
      <tr>
							<td colspan="5" class="text-right"><?php echo Utilities::getLabel('L_Cart_Total')?></td>
							<td class="text-right"><?php echo $currencyObj->format($cart_total_without_tax,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></th>
						</tr>
                        <tr>
                            <td colspan="5" class="text-right"><?php echo Utilities::getLabel('L_Delivery')?></td>
                            <td class="text-right">+<?php echo $currencyObj->format($order_detail["opr_shipping_charges"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><?php echo Utilities::getLabel('L_VAT')?></span></td>
                            <td class="text-right">+<?php echo $currencyObj->format($order_detail["opr_tax"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                        </tr>
                        <?php if ($order_detail["opr_refund_qty"]>0):?>
                        <tr>
                            <td colspan="5" class="text-right"><?php echo Utilities::getLabel('L_Order_Total')?></td>
                            <td class="text-right"><?php echo $currencyObj->format($order_detail["opr_net_charged"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?>
</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right"><?php echo Utilities::getLabel('L_Refund_Amount')?> [<?php echo Utilities::getLabel('L_Refund_Qty')?>.</span> - <?php echo $order_detail["opr_refund_qty"]?>] </td>
                            <td class="text-right"><?php echo $currencyObj->format($order_detail["opr_total_refund_amount"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-right"><strong><?php echo Utilities::getLabel('L_Order_Total')?></strong></td>
                            <td class="text-right"><strong><?php echo $currencyObj->format($order_detail["opr_net_charged"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></strong></td>
                        </tr>
                        <?php endif;?>
              </tbody>
            </table>
           
       </div>
