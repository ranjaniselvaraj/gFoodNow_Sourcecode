<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
	<?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
	<div class="fixed-container">
		<div class="dashboard">
			<?php include CONF_THEME_PATH . $controller.'/_partial/account_supplierleftpanel.php'; ?>
			<div class="data-side">
				<?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
				<h3>My Subscriptions</h3>
				<?=Message::getHtml();?>
				
				<div class="darkgray-form">
					<div class="tabs-form">
					  <div class="tabz-content">
						<?php echo str_replace("<br>", " ", $frmSearchForm->getFormHtml()); ?>
					  </div>
					</div>
				  </div>
				
				<div class="tbl-listing">
					<table>
						<tbody>
						<tr>
							<th width="20%"><?php echo Utilities::getLabel('L_Inv_Number')?></th>
							<th width="15%"><?php echo Utilities::getLabel('L_Date')?></th>
							<th width="35%"><?=Utilities::getLabel('L_Name')?></th>
                            <th width="15%"><?=Utilities::getLabel('L_Commission')?></th>
							<th width="15%"><?=Utilities::getLabel('L_Total')?></th>
							<th width="15%"><?=Utilities::getLabel('L_Status')?></th>
							<th width="10%"><?=Utilities::getLabel('L_Action')?></th>
						</tr>
						<? foreach ( $orders as $sn => $order ){ /* printArray($order); */ ?>
						<tr>
							<td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Inv_Number')?></span><?=$order['mporder_invoice_number'];?></td>
							<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Date')?></span><?=Utilities::formatDate($order['mporder_date_added'])?></td>
							<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Name')?></span><?=$order['mporder_merchantpack_name'].' - '.$order['mporder_merchantsubpack_name']?></td>
							<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Commission')?></span><?=$order['mporder_merchantpack_commission'];?>%</td>
                            <td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Total')?></span><?=Utilities::displayMoneyFormat($order['mporder_merchantsubpack_subs_amount']);?></td>
							<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Status')?></span><?=$order['sorder_status_name']?></td>
							<td class="cellitem"><span class="cellcaption"><?=Utilities::getLabel('L_Action')?></span><a href="<?=Utilities::generateUrl('account','view_subscription',array($order['mporder_id']));?>" class="btn small"><?=Utilities::getLabel('L_View')?></a></td>
						</tr>
						<? } 
						if( !$orders ){
							?>
							<tr>
								<td colspan="6">No Records Found!</td>
							</tr>
							<?
						}
						?>
						
						</tbody>
					</table>
					
					<? if ($pages>1):?>
					<div class="pager">
					  <ul>
					  <?=getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx);">xxpagexx</a></li>', $pages, $page,'<li class="active"><a  href="javascript:void(0)">xxpagexx</a></li>', '<li>...</li>');?>
					  </ul>
					</div>
					<? endif;?>
					
				</div>
			</div>
		</div>
	</div>
</div>