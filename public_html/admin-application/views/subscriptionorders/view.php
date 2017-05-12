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
                        <div class="sectionhead"><h4>View Subscription</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('subscriptionorders'); ?>">Back to Subscriptions</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						<div class="sectionbody"> 
						<div class="box_content clearfix toggle_container">
						<table class="table">
							<tr>
								
								<th>Invoice Number</th>
                                <th>IP Address</th>
								<th>Subscription Name</th>
								<th>Subscription Added On</th>
								<th>Subscription Period </th>
							</tr>
							<tr>
								<td><?=$order['mporder_invoice_number'];?></td>
                                <td><?=$order["mporder_ip_address"]?> </td>
								
								<td><?=$order['mporder_merchantpack_name'].' - '.$order['mporder_merchantsubpack_name']?></td>
								<td><?=Utilities::formatdate($order["mporder_date_added"])?></td>
								<td><?php if ($order['mporder_subscription_start_date']!='0000-00-00 00:00:00') {  echo Utilities::formatdate($order['mporder_subscription_start_date']).' to '.Utilities::formatdate($order['mporder_subscription_end_date']); }else{ echo '-NA-'; } ?></td>
							</tr>
							<tr>
								<th>Subscription Status</th>
								<th>Discount Coupon</th>
								<th>Payment Method</th>
								<th>Payment Status</th>
								<th>Maximum Products Upload Limit</th>
							</tr>
							<tr>
								<td><?=$order['sorder_status_name'];?></td>
								<td><?=Utilities::displayNotApplicable($order['mporder_discount_coupon'])?></td>
								<td><?=Utilities::displayNotApplicable($order["mporder_payment_method"])?></td>
								<td><?=$payment_status_arr[$order["mporder_payment_status"]]?></td>
								<td><?=$order['mporder_merchantpack_max_products']?></td>
							</tr>
							<tr>
								<th>Subscription Amount</th>
								<th>Discount Total</th>
								<th>Net Charged</th>
								<th>Recurring / Billing Cycle</th>
								<th>Profile Reference</th>
							</tr>
							<tr>
								<td><?=Utilities::displayMoneyFormat($order["mporder_merchantsubpack_subs_amount"])?></td>
								<td><?=Utilities::displayMoneyFormat($order["mporder_discount_total"])?></td>
								<td><strong><?=Utilities::displayMoneyFormat($order["mporder_net_charged"])?></strong></td>
								<td><strong><?=Utilities::displayMoneyFormat($order["mporder_recurring_chargeble_amount"])?></strong> / <?=$order["mporder_recurring_billing_cycle_frequency"]?> Days</td>
								<td><?php echo $order["mporder_gateway_subscription_id"];?></td>
							</tr>
							
						</table>		  
						</div>
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
								<td><a href="<?php echo Utilities::generateUrl('users','customer_form',array($order["mporder_user_id"]));?>"><?=$order["mporder_user_name"]?></a></th>
								<td><strong>Email</strong></td>
								<td><?=$order["mporder_user_email"]?></th>
							</tr>
							<tr>
								<td><strong>Phone Number</strong></td>
								<td><?=$order["mporder_user_phone"]?></td>
								<td></td>
								<td></td>
								
							</tr>
						</table>
						</div>
					</section>		
					
					<?php if (count($order["comments"])>0):?>
					<section class="section">
						<div class="sectionhead">
							<h4>Subscription Status History</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">							
							<tr>
								<th>Date Added</th>
								<th>Customer Notified</th>
								<th>Status</th>
							</tr>
							<?php foreach ($order["comments"] as $key=>$val){?>
							<tr>
								<td><?php echo Utilities::formatDate($val["mpos_history_date_added"])?></td>
								<td><?php echo $val["mpos_history_customer_notified"]==1?"Y":"N"?></td>
								<td><?php echo $val["sorder_status_name"]?></td>
							</tr>
							<?php } ?>
							</table>
						</div>
					</section>	
					<?php endif;?>
					<?php if (count($order["transactions"])>0):?>
					<section class="section">
						<div class="sectionhead">
							<h4>Transactions</h4>																
						</div>
						<div class="sectionbody">
							<table class="table">							
							<tr>
								<th width="10%">Txn ID</th>
								<th width="10%">Payment Gateway Txn. ID</th>
                                <th width="10%">Mode</th>
								<th width="10%">Date Added</th>
								<th width="10%">Status</th>
								<th width="15%">Amount</th>
							</tr>
							<?php foreach ($order["transactions"] as $val){
								$txn_amount = Utilities::displayMoneyFormat($val['mptran_amount']);
								$txn_amount = isset($val['mptran_gateway_response']['payment_cycle']) ? $txn_amount.' '.$val['mptran_gateway_response']['payment_cycle'] : $txn_amount;
							?>
							<tr>
								<td><?=$val["mptran_id"]?></td>
								<td><?=Utilities::displayNotApplicable($val["mptran_gateway_transaction_id"])?></td>
                                <td><?=Utilities::displayNotApplicable($val["mptran_mode"])?></td>
								<td><?=Utilities::formatdate($val["mptran_date"])?></td>
								<td><?=Utilities::displayNotApplicable($val['mptran_payment_status']);?></td>
								<td><?=$txn_amount?></td>
							</tr>
							<?php } ?>
							</table>
						</div>
					</section>
					 <?php endif;?>
					 
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>	