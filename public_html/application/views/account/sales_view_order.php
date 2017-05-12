<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>	
          <div class="box-head no-print">
            <h3><?php echo Utilities::getLabel('L_View_Sale_Order')?></h3>
            <div class="padding20 fr"> <a href="<?php echo Utilities::generateUrl('account', 'sales')?>" class="btn small"><?php echo Utilities::getLabel('L_My_Sales')?></a> <a target="_blank" href="<?php echo Utilities::generateUrl('account', 'sales_print_order',array($order_detail['opr_id']))?>" class="btn small secondary-btn "><?php echo Utilities::getLabel('L_Print_Order')?></a></div>
          </div>
          
          <div class="space-lft-right">
            <table class=" tbl-normal">
              <tbody>
                <tr>
                  <td><span class="itemcaption"><?php echo Utilities::getLabel('L_Date')?></span> <?php echo Utilities::formatDate($order_detail["order_date_added"])?></td>
                  <td><span class="itemcaption"><?php echo Utilities::getLabel('L_Invoice_Id')?></span> <?php echo $order_detail["opr_order_invoice_number"]?></td>
                  <td><span class="itemcaption"><?php echo Utilities::getLabel('L_Status')?></span> <?php echo $order_detail["orders_status_name"]?></td>
                </tr>
                <?php 
					$cart_total_without_tax=($order_detail["opr_customer_buying_price"]+$order_detail["opr_customer_customization_price"])*$order_detail["opr_qty"]; 
					$vat=$cart_total_with_tax-$cart_total_without_tax;
				?>
                <tr>
                  <td><span class="itemcaption"><?php echo Utilities::getLabel('L_Cart_Total')?> </span> <?php echo $currencyObj->format($cart_total_without_tax,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td><span class="itemcaption"><?php echo Utilities::getLabel('L_Delivery')?></span> +<?php echo $currencyObj->format($order_detail["opr_shipping_charges"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td><span class="itemcaption"><?php echo Utilities::getLabel('L_VAT')?></span> +<?php echo $currencyObj->format($order_detail["opr_tax"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                <tr>
                  <?php if ($order_detail["opr_refund_qty"]>0):?>
                  <td>
                  	<span class="itemcaption"><?php echo Utilities::getLabel('L_Order_Total')?></span> <?php echo $currencyObj->format($order_detail["opr_net_charged"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?>
                  </td>
                  <td>
                      <span class="itemcaption"><?php echo Utilities::getLabel('L_Refund_Qty')?>.</span> [<?php echo $order_detail["opr_refund_qty"]?>]
				  </td>
                  <td>
                      <span class="itemcaption"><?php echo Utilities::getLabel('L_Refund_Amount')?></span> <?php echo $currencyObj->format($order_detail["opr_total_refund_amount"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?>
                  </td>
                  <? else: ?>
                  <td colspan="3"><span class="itemcaption"><?php echo Utilities::getLabel('L_Order_Total')?></span> <?php echo $currencyObj->format($order_detail["opr_net_charged"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <?php endif;?>
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
                  <th><?php echo Utilities::getLabel('L_Shipping')?></th>
                  <th><?php echo Utilities::getLabel('L_Total')?></th>
                </tr>
                <?php 	
				  $opr_customer_buying_price=$order_detail["opr_customer_buying_price"];
				  $customization_price=$order_detail["opr_customer_customization_price"];
				  $adds=" (+".$currencyObj->format($order_detail["opr_customization_price"],$order_detail["order_currency_code"],$order_detail["order_currency_value"]).")";
				  $sku_codes=$oval['opr_code']!=""?$order_detail['opr_code']:$order_detail['opr_sku'];
				  $options=Utilities::renderHtml($order_detail["opr_customization_string"]);
				  $opr_customer_buying_price+=$customization_price;
				  $ind_price_total=$order_detail["opr_qty"]*$opr_customer_buying_price+$order_detail["opr_shipping_charges"];
				  
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
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Shipping')?></span><?php echo $order_detail["opr_shipping_required"]?$order_detail["opr_shipping_label"]:'-NA-';?> <?php echo $order_detail["shipping_company_name"]?'<i>'.Utilities::getLabel('L_By').'</i> '.$order_detail["shipping_company_name"]:'';?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Listed_Price')?> </span><?php echo $currencyObj->format($order_detail["opr_customer_buying_price"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Qty')?></span><?php echo $order_detail["opr_qty"]?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Shipping')?></span><?php echo $currencyObj->format($order_detail["opr_shipping_charges"],$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                  <td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Total')?></span><?php echo $currencyObj->format($ind_price_total,$order_detail["order_currency_code"],$order_detail["order_currency_value"])?></td>
                </tr>
                
              </tbody>
            </table>
            <div class="gap"></div>
            <table class=" tbl-normal">
              <tbody>
                <tr>
                  <th><?php echo Utilities::getLabel('L_Billing_Details')?>  </th>
                  <?php if ($order_detail['opr_shipping_required']) {?>
                  <th><?php echo Utilities::getLabel('L_Shipping_Details')?></th>
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
            
            
         <?php if (count($order_detail["comments"])>0):?>
			<div class="gap"></div> 
            <table border="0" class="tbl-normal no-print" cellpadding="0" cellspacing="0">
            <tr>
                <th width="15%"><?php echo Utilities::getLabel('L_Date_Added')?></th>
                <th width="20%"><?php echo Utilities::getLabel('L_Customer_Notified')?></th>
                <th width="20%"><?php echo Utilities::getLabel('L_Status')?></th>
                <th><?php echo Utilities::getLabel('L_Comments')?></th>
            </tr>
            <?php foreach ($order_detail["comments"] as $key=>$val){ 
			
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
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span><?php echo $val["orders_status_name"]?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Comments')?></span><?php echo Utilities::renderHtml(nl2br($comments),true)?></td>
              </tr>
            <?php } ?>
           </table>
		 <?php endif;?>   
       </div>
       	  <?php 
		  if ($display_form) {?>  	
          <h3><?php echo Utilities::getLabel('L_Order_Comments')?></h3>
          <div class="space-lft-right">
            <div class=" wrapform">
            	<?php echo $frm->getFormHtml(); ?>
              </div>
          </div>
          <div class="gap"></div>
          <?php } ?>
        </div>
        
      </div>
    </div>
  </div>
