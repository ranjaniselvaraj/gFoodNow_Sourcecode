<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
	<?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
	<div class="fixed-container">
		<div class="dashboard">
			<?php include CONF_THEME_PATH . $controller.'/_partial/account_supplierleftpanel.php'; ?>
			<div class="data-side">
				<?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
				<div class="box-head no-print">
					<h3><?=Utilities::getLabel('L_My_Subscriptions');?></h3>
					
					<div class="padding20 fr lising-btns"> 
					<? if($can_cancel_plan){ ?>
						<a href="<?=Utilities::generateUrl('account','cancel_subscription',array($order['mporder_id'])); ?>" onclick="return(confirm('<?php echo Utilities::getLabel('L_Are_you_sure_cancel_subscription')?>'));" class="btn small"><?=Utilities::getLabel('L_Deactivate_Subscription');?></a> 
					<? } 
					if($can_update_plan){ ?><a href="javascript:void(0)" data="<?=Utilities::getLabel('L_IT_WILL_CANCEL_CURRENT_ACTIVE_SUBSCRIPTION');?>" onClick="ChangeSubscription(this,'<?php echo $order['mporder_id']; ?>');" class="btn small"><?=Utilities::getLabel('L_Change_Plan');?></a><? }
					
					?>
					
					<a href="<?=Utilities::generateUrl('account', 'subscriptions')?>" class="btn small ">&laquo;&laquo; <?=Utilities::getLabel('L_Back_to_Subscriptions');?></a> </div>
				</div>
				
				
				<div class="space-lft-right resp-tab-content">
					<table class=" tbl-normal">
						<tbody>
							<tr>
								<td width="50%"><strong><?=Utilities::getLabel('L_Subscription_ID')?>:</strong></td>
								<td width="50%"><?=$order['mporder_id'];?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Subscription_Name')?>:</strong></td>
								<td><?=$order['mporder_merchantpack_name'].' - '.$order['mporder_merchantsubpack_name']?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Added_on')?>:</strong></td>
								<td><?=Utilities::formatDate($order['mporder_date_added']);?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Subscription_Period')?>:</strong></td>
								<td><?=Utilities::renderHtml($order['subscription_period']); ?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Subscription_Amount')?>:</strong></td>
								<td><?=Utilities::displayMoneyFormat($order['mporder_merchantsubpack_subs_amount']);?></td>
							</tr>
                            <tr>
								<td><strong><?=Utilities::getLabel('L_Commission')?>:</strong></td>
								<td><?=$order['mporder_merchantpack_commission'];?>%</td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Subscription_Status');?>:</strong></td>
								<td><?=$order['sorder_status_name']?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Payment_Method');?>:</strong></td>
								<td><?=Utilities::displayNotApplicable($order['payment_methods'])?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Profile_Reference');?>:</strong></td>
								<td><?=$order['mporder_gateway_subscription_id']?></td>
							</tr>
							<tr>
								<td><strong><?=Utilities::getLabel('L_Products_upload_Limit')?>:</strong></td>
								<td><?=$order['mporder_merchantpack_max_products']?></td>
							</tr>
						</tbody>
					</table>
					<?php if (count($transactions)>0 && (!empty($transactions))):?>	
					<div id="pp_subscription_detail"></div>
					<?=$frmTxnSearchForm->getFormHtml(); ?>
					<h3><?=Utilities::getLabel('L_Transactions');?></h3>
					<div class="tbl-listing">
                    	
						<table>
							<tbody>
							<tr>
								<th width="17%"><?=Utilities::getLabel('L_Payment_Gateway_Txn._ID')?></th>
                                <th width="15%"><?=Utilities::getLabel('L_Payment_Mode')?></th>
								<th width="15%"><?=Utilities::getLabel('L_Date')?></th>
								<th width="10%"><?php echo Utilities::getLabel('L_Status');?></th>
								<th width="10%"><?php echo Utilities::getLabel('L_Amount');?></th>
							</tr>
							<? foreach( $transactions as $key=>$transaction ){ 
								$txn_amount = Utilities::displayMoneyFormat($transaction['mptran_amount']);
								$txn_amount = isset($transaction['mptran_gateway_response']['payment_cycle']) ? $txn_amount.' '.$transaction['mptran_gateway_response']['payment_cycle'] : $txn_amount;
							?>
							<tr>
								
								<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Payment_Gateway_Txn._ID')?></span><?=Utilities::displayNotApplicable($transaction['mptran_gateway_transaction_id']);?></td>
								<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Payment_Mode')?></span><?=$transaction['mptran_mode']?></td>
                                <td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Date')?></span><?=displayDate($transaction['mptran_date'],true)?></td>
								<td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Status');?></span><?=Utilities::displayNotApplicable($transaction['mptran_payment_status']);?></td>
								<td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Amount');?></span><?=$txn_amount?></td>
							</tr>
							<? } ?>
							</tbody>
						</table>
                        
						<? if ($pages>1):?>
						<div class="pager">
						  <ul>
						  <?=getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx);">xxpagexx</a></li>', $pages, $page,'<li class="active"><a  href="javascript:void(0)">xxpagexx</a></li>', '<li>...</li>');?>
						  </ul>
						</div>
						<? endif;?>
                        <?php endif;?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>