<?php defined('SYSTEM_INIT') or die('Invalid Usage'); $order_obj = new Orders($order["order_id"]);?> 
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
					<?php if ($not_eligible):?>
					<section class="section">	
						<div class="sectionhead"><h4>Information</h4></div>	
						<div class="sectionbody">	<div class="notification failure">							
							<p> <?php echo sprintf(Utilities::getLabel('L_this_order_already'),"<i>".$order["orders_status_name"]."</i>")?>
							</p>
						</div>
						</div>
					</section>
					<?php endif; ?>
					<section class="section">
                        <div class="sectionhead"><h4>Cancel Vendor Order</h4>							
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('vendororders'); ?>">Back to Vendor Orders</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						<div class="sectionbody"> 
							<div class="box_content clearfix toggle_container">
							 <table class="table ordertable">
							  <tr>
									<th>Invoice Id</th>
									<th>Order Date </th>
									<th>Status </th>
									<th><?php if ($order["opr_refund_qty"]>0):?>Refund for Qty. [<?php echo $order["opr_refund_qty"]?>] <?php endif; ?></th>
							  </tr>
							  <tr>
									<td><?php echo $order["opr_order_invoice_number"]?></td>
									<td><?php echo Utilities::formatDate($order["order_date_added"])?></td>
									<td><?php echo $order["orders_status_name"]?></td>
									<td><?php if ($order["opr_refund_qty"]>0): echo $currencyObj->format($order["opr_total_refund_amount"],$order['order_currency_code'],$row['order_currency_value']); endif; ?></td>
								</tr>
								<tr>
									<th>Customer/Guest</th>
									<th>Payment Method</th>
									<th>Commission (Tax Inc.) [<?php echo $order["opr_commission_percentage"]?>%]</th>
									<th>Affiliate Commission [<?php echo $order["opr_affiliate_commission_percentage"]?>%]</th>
								</tr>
							  <tr>
									<td><a href="<?php echo Utilities::generateUrl('users','customer_form',array($order["order_user_id"]));?>"><?php echo $order["order_user_name"]?></a></td>
									<td><?php echo Utilities::displayNotApplicable($order["payment_methods"])?></td>
									<td><strong><?php echo $currencyObj->format(($order["opr_commission_charged"]-$order["opr_refund_commission"]),$order['order_currency_code'],$order['order_currency_value'])?></strong></td>
									<td><strong><?php echo $currencyObj->format($order["opr_affiliate_commission"],$order['order_currency_code'],$order['order_currency_value'])?></strong> 
                                    <? if ($order["affiliate_id"]>0) {?>
                                    [<a href="<?php echo Utilities::generateUrl('affiliates','form',array($order["affiliate_id"]));?>"><?php echo $order["affiliate_name"]?></a>]
                                    <? } ?>
                                    </td>
								</tr>
								<tr>
									<th>Cart Total </th>
									<th>Delivery</th>
									<th>VAT</th>
									<th>Total Paid</th>
								</tr>
								<?php 
									$cart_total_with_tax=($order["opr_customer_buying_price"]+$order["opr_customer_customization_price"])*$order["opr_qty"]; 
									$cart_total_without_tax=$cart_total_with_tax*100/(100+$order["order_vat_perc"]);
									$vat=$cart_total_with_tax-$cart_total_without_tax;
								
								?>
								 <tr>
									<td><?php echo $currencyObj->format(($order["opr_qty"] * ($order["opr_customer_buying_price"] + $order["opr_customer_customization_price"])),$order['order_currency_code'],$order['order_currency_value'])?></td>
									<td>+<?php echo $currencyObj->format($order["opr_shipping_charges"],$order['order_currency_code'],$order['order_currency_value'])?></td>
									<td>+<?php echo $currencyObj->format($order["opr_tax"],$order['order_currency_code'],$order['order_currency_value'])?></td>
									<td><strong><?php echo $currencyObj->format($order["opr_net_charged"],$order['order_currency_code'],$order['order_currency_value'])?></strong></td>
								</tr>
							</table>
                        
	                        </div>
						</div>	
					</section>
					<section class="section">
						<div class="sectionhead">
							<h4>Vendor / Customer Details</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">
								<tr>
									<th>Vendor Details</th>
									<th>Customer Details</th>
								</tr>
								<tr>
									<td><?php echo $order["opr_shop_owner_name"]?><br/><strong>E:</strong> <?php echo $order["opr_shop_owner_email"]?><br/><strong>P:</strong> <?php echo $order["opr_shop_owner_phone"]?></td>
								   <td><?php echo $order["order_user_name"]?><br/><strong>E:</strong> <?php echo $order["order_user_email"]?><br/><strong>P:</strong> <?php echo $order["order_user_phone"]?></td>
								</tr>		
							</table>
						</div>
					</section>	
					<section class="section">
						<div class="sectionhead">
							<h4>Order Details </h4>																
						</div>
						<div class="sectionbody">
							<table class="table">
							  <tr>
								<th>#</td>
								<th>Product Name</th>
								<th>Shipping</th>
								<th>Listed Price </th>
								<th>Buying Price </th>
								<th>Qty</th>
								<th>Shipping</th>
								<th>Tax</th>
								<th>Total</th>
							  </tr>
							  <?php 
								  $sku_codes=$order['opr_sku'];
						  $customization_price=$order["opr_customer_customization_price"];
						  $adds=" (+".$currencyObj->format($customization_price,$order['order_currency_code'],$order['order_currency_value']).")";
						  $options=Utilities::renderHtml($order["opr_customization_string"]);
						  $customization_cost = (count($order['order_options'])>0)?"<br/><strong>Combination Selected</strong>$adds":"";
						  //$options=(($options=="")?"":"<br/><strong>Combination Selected</strong>$adds").Utilities::renderHtml($options);
							  ?>
							  <tr>
								 <td>#</td>
								<td><?php echo $order["opr_name"]?><?php echo $customization_cost?><?php foreach ($order['order_options'] as $option) { ?>
               					 <br />
					                <?php if ($option['type'] != 'file') { ?>
					                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
				                	<?php } else { ?>
		           						&nbsp;<small> - <?php echo $option['name']; ?>: <a href="<?php echo $option['href']; ?>"><?php echo $option['value']; ?></a></small>
	        				       	 <?php } ?>
				                <?php } ?><br/><strong>SKU</strong>: <?php echo $sku_codes?><br/></td>
								<td><?php echo $order["opr_shipping_required"]?$order["opr_shipping_label"]:'-NA-';?></td>
								<td><?php echo $currencyObj->format($order["opr_sale_price"],$order['order_currency_code'],$order['order_currency_value'])?></td>
								<td><?php echo $currencyObj->format($order["opr_customer_buying_price"],$order['order_currency_code'],$order['order_currency_value'])?></td>
								<td><?php echo $order["opr_qty"]?></td>
								<!--<td><?php echo $order["opr_ship_free"]==1?"Y":"N"?></td>-->
								<!--<td><?php echo $order["opr_tax_free"]==1?"Y":"N"?></td>-->
								<td><?php echo $currencyObj->format($order["opr_shipping_charges"],$order['order_currency_code'],$order['order_currency_value'])?></td>
								<td><?php echo $currencyObj->format($order["opr_tax"],$order['order_currency_code'],$order['order_currency_value'])?></td>
								<td><?php echo $currencyObj->format($order["opr_net_charged"],$order['order_currency_code'],$order['order_currency_value'])?></td>
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
								<td valign="top"><?php if ($order["opr_shipping_required"]) { echo '<strong>'.$order["order_shipping_name"].'</strong><br/>'.((strlen($order['order_shipping_address1']) > 0)?$order['order_shipping_address1']:'') .((strlen($order['order_shipping_address2']) > 0)?'<br/>'.$order['order_shipping_address2']:'') . ((strlen($order['order_shipping_city']) > 0)?'<br/>'.$order['order_shipping_city'] . ', ':'') . $order['order_shipping_postcode']; ?><br><?php echo $order['order_shipping_state']; ?>, <?php echo $order['order_shipping_country'].((strlen($order['order_shipping_phone']) > 0)?'<br/><strong>T</strong>:'.$order['order_shipping_phone']:'');  } ?></td>
							</tr>
						</table>						
						</div>
					</section>
					<?php if (count($order["comments"])>0):?>
					<section class="section">						
						<div class="sectionbody">
						<table class="table">
						<tr>
							<th width="10%">Date Added</th>
							<th width="15%">Customer Notified</th>
							<th width="15%">Status</th>
							<th width="60%">Comments</th>
						</tr>
						<?php
						foreach ($order["comments"] as $key=>$val){ $comments=$val["comments"];
						if ($val['tracking_number']){
                       		$comments=!empty($comments)?$comments."<br/><br/>":"";
							$shipping_method = !empty($val["scompany_name"])?$val["scompany_name"]:$val["opr_shipping_label"];
							$comments=$comments.Utilities::getLabel('M_Shipment_Information').": ".Utilities::getLabel('M_Tracking_Number')." ".$val['tracking_number']." VIA <em>".$shipping_method."</em><br/>";
                        } ?>
						<tr>
						   <td><?php echo Utilities::formatDate($val["date_added"])?></td>
							<td><?php echo $val["customer_notified"]==1?"Y":"N"?></td>
							<td><?php echo $val["orders_status_name"]?></td>
							<td><?php echo Utilities::renderHtml(nl2br($comments))?></td>
						</tr>
						<?php } ?>
						</table>
						</div>
					</section>
					<?php endif;?>		
					<?php if (!(isset($not_eligible))):?> 
					<section class="section">
						<div class="sectionhead">
							<h4>Reason for cancellation </h4>																
						</div>
						<div class="sectionbody"><?php echo $frm->getFormHtml(); ?></div>
					</section>							                     
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>	