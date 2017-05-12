<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $payment_status_arr;?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<?php echo html_entity_decode($breadcrumb); ?>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>View Order</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('orders'); ?>">Back to Orders</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						<div class="sectionbody"> 
						<div class="box_content clearfix toggle_container">
						<table class="table ordertable">
							<tr>
								<th>Invoice ID</th>
								<th>Payment Status </th>
								<th>Customer/Guest</th>
								<th>Payment Method</th>
								<th>Order Date</th>
								<th>Site Commission</th>
							</tr>
							<tr>
								<td><?php echo $order["order_invoice_number"]?></td>
								<td><?php echo $payment_status_arr[$order["order_payment_status"]]?></td>
								<td><a href="<?php echo Utilities::generateUrl('users','customer_form',array($order["order_user_id"]));?>"><?php echo $order["order_user_name"]?></a></td>
								<td><?php echo Utilities::displayNotApplicable($order["payment_methods"])?></td>
								<td><?php echo Utilities::formatDate($order["order_date_added"])?></td>
								<td><?php echo $currencyObj->format($order["order_site_commission"],$order['order_currency_code'],$order['order_currency_value'])?></td>
							</tr>
							<tr>
								<th>Affiliate Commission</th>
								<th>Referrer Rewards</th>
								<th>Referral Rewards</th>
								<th>Payment(s) Realized</th>
								<th>Balance Payment</th>
                                <th></th>
							</tr>
							<tr>
                                <td><?php echo $currencyObj->format($order["order_affiliate_commission"],$order['order_currency_code'],$order['order_currency_value'])?>
                                <? if ($order["affiliate_id"]>0) {?>
                                [<a href="<?php echo Utilities::generateUrl('affiliates','form',array($order["affiliate_id"]));?>"><?php echo $order["affiliate_name"]?></a>]
                                <? } ?>
                                </td>
                                <td><?php echo $order["order_referrer_reward_points"]?> <? if (!empty($order["referrer_name"])) {?> [<a href="<?php echo Utilities::generateUrl('users','customer_form',array($order["order_referrer_id"]));?>"><?php echo $order["referrer_name"]?></a>] <? } ?></td>
                                <td><?php echo $order["order_referee_reward_points"]?></td>
								<td><?php echo $currencyObj->format($order["totPayments"],$order['order_currency_code'],$order['order_currency_value'])?></td>
								<td><?php echo $currencyObj->format($order["order_balance"],$order['order_currency_code'],$order['order_currency_value'])?></td>
                                <td></td>
							</tr>
							
						</table>		  
						</div>
						</div>	
					</section>
					<section class="section">
						<div class="sectionhead">
							<h4>Order Details</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">
							<tr>
								<th>#</td>
								<th>Product Name</th>
								<th>Shipping</th>
								<th>Listed Price</th>
								<th>Buying Price</th>
								<th>Qty</th>
								<th class="text-right">Shipping</th>
								<th class="text-right">Total</th>
							</tr>
							<?php $incr=0; foreach($order["products"] as $okey=>$oval):
								$incr++;		
								$opr_customer_buying_price=$oval["opr_customer_buying_price"];
								$customization_price=$oval["opr_customer_customization_price"];
								$adds=" (+".$currencyObj->format($customization_price,$oval['order_currency_code'],$oval['order_currency_value']).")";
								$sku_codes=$oval['opr_sku'];
								$options=Utilities::renderHtml($oval["opr_customization_string"]);
								//$options=(($options=="")?"":"<br/><strong>Combination Selected</strong>$adds").$options;
								$customization_cost = (count($oval['order_options'])>0)?"<br/><strong>Combination Selected</strong>$adds":"";
								$opr_customer_buying_price+=$customization_price;
								$ind_price_total=$oval["opr_qty"]*$opr_customer_buying_price+$oval["opr_shipping_charges"];
							?>
							<tr>
								<td><?php echo $incr;?></td>
								<!--<td><?php echo $oval["opr_name"]?><?php echo $options?><br/><strong>Code</strong>: <?php echo $sku_codes?><br/><strong>Vendor</strong>: <?php echo $oval["opr_shop_owner_username"]!=""?$oval["opr_shop_owner_username"]:"-NA-"?></td>-->
                                <td><?php echo $oval["opr_name"]?><?php echo $customization_cost?>
                                <?php foreach ($oval['order_options'] as $option) { ?>
               					 <br />
					                <?php if ($option['type'] != 'file') { ?>
					                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
				                	<?php } else { ?>
		           						&nbsp;<small> - <?php echo $option['name']; ?>: <a href="<?php echo $option['href']; ?>"><?php echo $option['value']; ?></a></small>
	        				       	 <?php } ?>
				                <?php } ?>
                                <br/><strong>SKU</strong>: <?php echo $sku_codes?><br/><strong>Vendor</strong>: <?php echo $oval["opr_shop_owner_username"]!=""?$oval["opr_shop_owner_username"]:"-NA-"?></td>
								<td><?php echo $oval["opr_shipping_required"]?$oval["opr_shipping_label"]:'-NA-';?></td>
								<td><?php echo $currencyObj->format($oval["opr_sale_price"],$oval['order_currency_code'],$oval['order_currency_value'])?></td>
								<td><?php echo $currencyObj->format($oval["opr_customer_buying_price"],$oval['order_currency_code'],$oval['order_currency_value'])?></td>
								<td><?php echo $oval["opr_qty"]?></td>
								<td class="text-right"><?php echo $currencyObj->format($oval["opr_shipping_charges"],$oval['order_currency_code'],$oval['order_currency_value'])?></td>
								<td class="text-right"><?php echo $currencyObj->format($ind_price_total,$oval['order_currency_code'],$oval['order_currency_value'])?></td>
							</tr>
						<?php endforeach;?>
                         
                        <tr>
							<td colspan="7" class="text-right">Cart Total</td>
							<td class="text-right"><?php echo $currencyObj->format($order["order_cart_total"],$order['order_currency_code'],$order['order_currency_value'])?></th>
						</tr>
                        <tr>
                            <td colspan="7" class="text-right">Delivery</td>
                            <td class="text-right">+<?php echo $currencyObj->format($order["order_shipping_charged"],$order['order_currency_code'],$order['order_currency_value'])?></td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right">VAT</td>
                            <td class="text-right">+<?php echo $currencyObj->format($order["order_tax_charged"],$order['order_currency_code'],$order['order_currency_value'])?></td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right">Discount Coupon [<?php echo Utilities::displayNotApplicable($order["order_discount_coupon"])?>]</td>
                            <td class="text-right">-<?php echo $currencyObj->format($order["order_discount_total"],$order['order_currency_code'],$order['order_currency_value'])?></td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right">Reward Points</td>
                            <td class="text-right">-<?php echo $currencyObj->format($order["order_reward_points"],$order['order_currency_code'],$order['order_currency_value'])?></td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right"><strong>Order Total</strong></td>
                            <td class="text-right"><strong><?php echo $currencyObj->format($order["order_net_charged"],$order['order_currency_code'],$order['order_currency_value'])?></strong></td>
                        </tr>
						
                        
				</table>
						
						</div>
					</section>
					<section class="section">
						<div class="sectionhead">
							<h4>Customer Details</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">							
							<tr>
								<td><strong>Name</strong></td>
								<td><?php echo $order["order_user_name"]?></th>
								<td><strong>Email</strong></td>
								<td><?php echo $order["order_user_email"]?></th>
							</tr>
							<tr>
								<td><strong>Phone Number</strong></td>
								<td><?php echo $order["order_user_phone"]?></td>
								<td><strong>FAX Number</strong></td>
								<td><?php echo $order["order_user_fax"]!=""?$order["order_user_fax"]:"-NA-"?></td>
							</tr>
						</table>
						</div>
					</section>		
					<section class="section">
						<div class="sectionhead">
							<h4>Billing / Shipping Details</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">
							<tr>
								<th>Billing Details  </th>
								<th>Shipping Details</th>
							</tr>
							<tr>
								<td valign="top"><?php echo '<strong>'.$order["order_billing_name"].'</strong><br/>'.((strlen($order['order_billing_address1']) > 0)?$order['order_billing_address1']:'') .((strlen($order['order_billing_address2']) > 0)?'<br/>'.$order['order_billing_address2']:'') . ((strlen($order['order_billing_city']) > 0)?'<br/>'.$order['order_billing_city'] . ', ':'') . $order['order_billing_postcode']; ?><br><?php echo $order['order_billing_state']; ?>, <?php echo $order['order_billing_country'].((strlen($order['order_billing_phone']) > 0)?'<br/><strong>T</strong>:'.$order['order_billing_phone']:''); ?></td>
								<td valign="top"><?php if ($order["order_shipping_required"]) { echo '<strong>'.$order["order_shipping_name"].'</strong><br/>'.((strlen($order['order_shipping_address1']) > 0)?$order['order_shipping_address1']:'') .((strlen($order['order_shipping_address2']) > 0)?'<br/>'.$order['order_shipping_address2']:'') . ((strlen($order['order_shipping_city']) > 0)?'<br/>'.$order['order_shipping_city'] . ', ':'') . $order['order_shipping_postcode']; ?><br><?php echo $order['order_shipping_state']; ?>, <?php echo $order['order_shipping_country'].((strlen($order['order_shipping_phone']) > 0)?'<br/><strong>T</strong>:'.$order['order_shipping_phone']:''); } ?></td>
							</tr>
						</table>
						</div>
					</section>	
					<?php if (count($order["comments"])>0):?>
					<section class="section">
						<div class="sectionhead">
							<h4>Order Status History</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">							
							<tr>
								<th width="10%">Date Added</th>
								<th width="15%">Customer Notified</th>
								<th width="15%">Payment Status</th>
								<th width="60%">Comments</th>
							</tr>
							<?php foreach ($order["comments"] as $key=>$val){?>
							<tr>
								<td><?php echo Utilities::formatDate($val["date_added"])?></td>
								<td><?php echo $val["customer_notified"]==1?"Y":"N"?></td>
								<td><?php echo $payment_status_arr[$val["orders_payment_status"]]?></td>
								<td><div class="break-me"><?php echo nl2br(Utilities::renderHtml($val["comments"]))?></div></td>
							</tr>
							<?php } ?>
							</table>
						</div>
					</section>	
					<?php endif;?>
					<?php if (count($order["payments"])>0):?>
					<section class="section">
						<div class="sectionhead">
							<h4>Order Payment History</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">							
							<tr>
								<th width="10%">Date Added</th>
								<th width="10%">Txn ID</th>
								<th width="15%">Payment Method</th>
								<th width="10%">Amount</th>
								<th width="15%">Comments</th>
								<th>Gateway Response</th>
							</tr>
							<?php foreach ($order["payments"] as $key=>$val){?>
							<tr>
								<td><?php echo Utilities::formatDate($val["op_date"])?></td>
								<td><?php echo $val["op_gateway_txn_id"]?></td>
								<td><?php echo $val["op_payment_method"]?></td>
								<td><?php echo $currencyObj->format($val["op_amount"],$order['order_currency_code'],$order['order_currency_value'])?></td>
								<td><?php echo nl2br(Utilities::renderHtml($val["op_comments"]))?></td>
								<td style="width:400px; word-wrap:break-word;">
									<div style="width:380px;overflow:auto">
										<?php echo nl2br(Utilities::renderHtml($val["op_gateway_response"]))?>
									</div></td>
							</tr>
							<?php } ?>
							</table>
						</div>
					</section>
					 <?php endif;?>
					<?php if (!$order["order_payment_status"]) {?>        
					<section class="section">
						<div class="sectionhead">
							<h4>Order Payments</h4>																
						</div>
						<div class="sectionbody"><?php echo $frm->getFormHtml(); ?></div>
					</section>	
					
				<?php } ?>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>	