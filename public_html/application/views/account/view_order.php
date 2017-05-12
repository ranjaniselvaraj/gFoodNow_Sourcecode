<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $payment_status_arr; ?>
<div class="body clearfix">
	
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          		
          <div class="box-head no-print">
            <h3><?php echo Utilities::getLabel('L_View_Order')?></h3>
            
            <div class="padding20 fr">  <a href="<?php echo Utilities::generateUrl('account', 'orders')?>" class="btn small ">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_orders')?></a> <a target="_blank" href="<?php echo Utilities::generateUrl('account', 'print_order',array($order_detail["order_id"],($child_order || $order_detail["primary_order"]!=1)?$order_detail["opr_id"]:''))?>" class="btn small secondary-btn "><?php echo Utilities::getLabel('L_Print_Order')?></a> </div>
          </div>
          <div class="space-lft-right">
            <div class="sortingbar clearfix">
              <aside class="grid_1"><span class="txtBold"><?php echo Utilities::getLabel('L_Order')?> <strong>#<?php echo $order_detail["invoice_number"]?></strong>
              	<?php if ($order_detail["opr_status"]==Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS")) { ?>
                	<a href="<?php echo Utilities::generateUrl('account', 'view_return_request',array($order_detail["refund_id"]))?>"><?php echo $order_detail["status_name"]?></a>
                <?php } else { echo $order_detail["status_name"]; } ?>
              </span>   </aside>
              
              <aside class="grid_2"><span class="txtBold"><?php echo Utilities::getLabel('L_Order_Date')?>: <strong><?php echo Utilities::formatDate($order_detail["order_date_added"])?></strong></span></aside>
            </div>
            <div class="areaaddress">
              <aside class="grid_1">
                <h3><?php echo Utilities::getLabel('L_Billing_Address')?></h3>
                <address class="colborder">
                <p>
			 <?php echo '<strong>'.$order_detail["order_billing_name"].'</strong><br/>'.((strlen($order_detail['order_billing_address1']) > 0)?$order_detail['order_billing_address1']:'') .((strlen($order_detail['order_billing_address2']) > 0)?'<br/>'.$order_detail['order_billing_address2']:'') . ((strlen($order_detail['order_billing_city']) > 0)?'<br/>'.$order_detail['order_billing_city'] . ', ':'') . $order_detail['order_billing_postcode']; ?><br><?php echo $order_detail['order_billing_state']; ?>, <?php echo $order_detail['order_billing_country'].((strlen($order_detail['order_billing_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['order_billing_phone']:'').((strlen($order_detail['order_billing_fax']) > 0)?'<br/><strong>F</strong>:'.$order_detail['order_billing_fax']:''); ?>
             </p><br/>
                <p><strong><?php echo Utilities::getLabel('L_Payment_Method')?></strong>: <?php echo Utilities::displayNotApplicable($order_detail["payment_methods"])?></p>
                </address>
              </aside>
              <?php if (($order_detail['opr_shipping_required'] && $child_order>0) || ($order_detail['order_shipping_required'] && $child_order==null)) {?>
              <aside class="grid_2">
                <h3><?php echo Utilities::getLabel('L_Shipping_Adddress')?></h3>
                <address class="colborder">
                <p><?php echo '<strong>'.$order_detail["order_shipping_name"].'</strong><br/>'.((strlen($order_detail['order_shipping_address1']) > 0)?$order_detail['order_shipping_address1']:'') .((strlen($order_detail['order_shipping_address2']) > 0)?'<br/>'.$order_detail['order_shipping_address2']:'') . ((strlen($order_detail['order_shipping_city']) > 0)?'<br/>'.$order_detail['order_shipping_city'] . ', ':'') . $order_detail['order_shipping_postcode']; ?><br><?php echo $order_detail['order_shipping_state']; ?>, <?php echo $order_detail['order_shipping_country'].((strlen($order_detail['order_shipping_phone']) > 0)?'<br/><strong>T</strong>:'.$order_detail['order_shipping_phone']:'').((strlen($order_detail['order_shipping_fax']) > 0)?'<br/><strong>F</strong>:'.$order_detail['order_shipping_fax']:''); ?></p>
                	
                    
                    
                    <br/>
                <!--<p><strong><?php echo Utilities::getLabel('L_Shipping_Method')?></strong>: <?php echo $order_detail["order_shipping_method"]?></p>-->
                </address>
              </aside>
              <?php } ?>
            </div>
            <table class=" tbl-normal">
              <tbody>
                <tr>
                    <th><?php echo Utilities::getLabel('L_Image')?></th>
                    <th><?php echo Utilities::getLabel('L_Product')?></th>
                    <th><?php echo Utilities::getLabel('L_Qty')?></th>
                    <th><?php echo Utilities::getLabel('L_Unit_Price')?></th>
                    <th><?php echo Utilities::getLabel('L_Subtotal')?></th>
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
                  <td class="itemtd"><span class="cellcaption"><?php echo Utilities::getLabel('L_Image')?></span>
                    <div class="box_square"><img src="<?php echo Utilities::generateUrl('image','product_image',array($row['opr_product_id'],'THUMB'))?>" alt=""></div></td>
                  <td class="titletd"><span class="cellcaption"><?php echo Utilities::getLabel('L_Product')?></span> 
                             <a href="<?php echo Utilities::generateUrl('shops','view',array($row["opr_product_shop"]))?>"><?php echo $row["opr_product_shop_name"];?></a>                  <br/>
                                
                  <span class="item_Title"><?php echo $row["opr_name"]?><?php echo  $customization_cost;?></span>					<?php foreach ($row['order_options'] as $option) { ?>
               					 <br />
					                <?php if ($option['type'] != 'file') { ?>
					                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
				                	<?php } else { ?>
		           						&nbsp;<small> - <?php echo $option['name']; ?>: <a href="<?php echo $option['href']; ?>"><?php echo $option['value']; ?></a></small>
	        				       	 <?php } ?>
				                <?php } ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Qty')?></span><?php echo $row["opr_qty"]?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Unit_Price')?></span><?php echo $currencyObj->format(($row["opr_customer_buying_price"] + $row["opr_customer_customization_price"]),$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Subtotal')?></span><?php echo $currencyObj->format( $row['opr_qty']*($row["opr_customer_buying_price"] + $row["opr_customer_customization_price"]),$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
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
                  <td class="mergedcell" colspan="4"><?php echo Utilities::getLabel('L_Cart_Total')?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Subtotal')?></span><?php echo $currencyObj->format($cart_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?> </td>
                </tr>
                <?php if ($tax>0):?> 
                <tr>
               		<td colspan="4" class="mergedcell"><?php echo Utilities::getLabel('L_VAT')?></td>
	                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_VAT')?></span>+ <?php echo $currencyObj->format($tax,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php endif;?> 
                  <tr>
                    <td colspan="4" class="mergedcell"><?php echo Utilities::getLabel('L_Shipping_Handling')?> </td>
                    <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Shipping_Handling')?></span>+ <?php echo $currencyObj->format($shipping_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  </tr>
				               
				<?php if ($discount>0):?>
                <tr>
                    <td colspan="4" class="mergedcell"><?php echo Utilities::getLabel('L_Coupon')?> (<?php echo $order_detail["order_discount_coupon"]?>)</td>
                    <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Coupon')?> (<?php echo $order_detail["order_discount_coupon"]?>)</span>- <?php echo $currencyObj->format($order_detail["order_discount_total"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php endif;?>
                <?php if ($reward_points>0):?>
                <tr>
                    <td colspan="4" class="mergedcell"><?php echo Utilities::getLabel('L_Reward_Points')?> (<?php echo $order_detail["order_reward_points"]?>)</td>
                    <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Reward_Points')?> (<?php echo $order_detail["order_reward_points"]?>)</span>- <?php echo $currencyObj->format($order_detail["order_reward_points"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <?php endif;?>
                <?php $grand_total=$cart_total+$shipping_total+$tax-$discount-$reward_points; ?>
                <tr class="trLast">
                	<td colspan="4" class="mergedcell"><?php echo Utilities::getLabel('L_Grand_Total')?></td>
	                <td><?php echo $currencyObj->format($grand_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
              	</tr>
	           <?php if (($order_detail["order_credits_charged"]>0) && ($order_detail["primary_order"]==0)){?>
               <tr>
                <td colspan="5" class="mergedcell">(<?php echo Utilities::getLabel('L_Credits_Used')?>: <?php echo $currencyObj->format($order_detail["order_credits_charged"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?> + <?php echo Utilities::getLabel('L_Paid_Amount')?> <?php echo $currencyObj->format($order_detail["order_actual_paid"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?>)</td>
              </tr>
              <?php }?> 
              <?php if ($order_detail["opr_refund_qty"]>0):?>
              <tr>
                <td colspan="4" class="mergedcell"><?php echo Utilities::getLabel('L_Refund_Amount_Qty')?>. [<?php echo $order_detail["opr_refund_qty"]?>]</td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Refund_Amount_Qty')?>. [<?php echo $order_detail["opr_refund_qty"]?>]</span> <?php echo $currencyObj->format($order_detail["opr_total_refund_amount"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?> </td>
              </tr>
              <?php endif;?>
              </tbody>
            </table>
            <?php 
			//die($order_detail["primary_order"]."=");
			if (count($order_detail["child_order_comments"])>0):?>
			<div class="gap"></div> 
            <table border="0" class="tbl-normal" cellpadding="0" cellspacing="0">
            <tr>
    			<th colspan="5"><?php echo Utilities::getLabel('L_Status_History')?></th>
		    </tr>
            <tr>
                <td width="15%"><strong><?php echo Utilities::getLabel('L_Date_Added')?></strong></td>
                <td width="20%"><strong><?php echo Utilities::getLabel('L_Customer_Notified')?></strong></td>
                <td width="20%"><strong><?php echo Utilities::getLabel('L_Status')?></strong></td>
                <td width="30%"><strong><?php echo Utilities::getLabel('L_Comments')?></strong></td>
            </tr>
            <?php foreach ($order_detail["child_order_comments"] as $key=>$val){ 
			
				$comments=$val["comments"];
				if ($val['tracking_number']){
                       		$comments=!empty($comments)?$comments."<br/><br/>":"";
							$shipping_method = !empty($val["scompany_name"])?$val["scompany_name"]:$val["opr_shipping_label"];
							$comments=$comments.Utilities::getLabel('M_Shipment_Information').": ".Utilities::getLabel('M_Tracking_Number')." ".$val['tracking_number']." VIA <em>".$shipping_method."</em><br/>";
				}
			
			?>
            <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date_Added')?></span><?php echo Utilities::formatDate($val["date_added"])?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Customer_Notified')?></span><?php echo $val["customer_notified"]==1?"Y":"N"?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span>
				<?php echo $val["orders_status_name"]; ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Comments')?></span>
                <div class="break-me"><?php echo Utilities::renderHtml(nl2br($comments),true)?></div></td>
              </tr>
            <?php } ?>
           </table>
		 <?php endif;?>   
        <div class="gap"></div>
        <?php if ((count($order_detail["payments"]) && ((int)$child_order==0))>0):?>
		<div class="gap"></div> 
	    <table border="0" class="tbl-normal no-print" cellpadding="0" cellspacing="0">
    		<tr>
    			<th colspan="5"><?php echo Utilities::getLabel('L_Payment_History')?></th>
		    </tr>
    		<tr>
	        <td nowrap="nowrap" ><strong><?php echo Utilities::getLabel('L_Date_Added')?></strong></td>
            <td nowrap="nowrap"><strong><?php echo Utilities::getLabel('M_Txn_ID')?></strong></td>
            <td nowrap="nowrap"><strong><?php echo Utilities::getLabel('L_Payment_Method')?></strong></td>
            <td><strong><?php echo Utilities::getLabel('L_Amount')?></strong></td>
            <td><strong><?php echo Utilities::getLabel('L_Comments')?></strong></td>
		   </tr>
    		<?php foreach ($order_detail["payments"] as $key=>$val){?>
            <tr>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date_Added')?></span><?php echo Utilities::formatDate($val["op_date"])?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('M_Txn_ID')?></span><?php echo $val["op_gateway_txn_id"]?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Payment_Method')?></span><?php echo $val["op_payment_method"]?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Amount')?></span><?php echo $currencyObj->format($val["op_amount"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Comments')?></span><?php echo nl2br(Utilities::renderHtml($val["op_comments"]))?></td>
              </tr>
		   <?php } ?>
    	</table>
    <?php endif;?>
    <br/>
    
          </div>
        </div>
        
      </div>
    </div>
  </div>
