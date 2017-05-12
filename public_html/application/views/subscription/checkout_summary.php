<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="tbl-summary">
                <thead>
                   <tr>
						<th width="80%"><?=Utilities::getLabel('M_Subscription_Name');?> </th>
						<th width="20%"><?=Utilities::getLabel('M_Price')?></th>
					</tr>
                </thead>
                <tbody>
                  <?php 
				  	foreach ($subpackages as $subpackage) { ?>
					<tr>
						<td><div class="product-name"><a href="<?php echo Utilities::generateUrl('account','packages')?>"><strong><?php echo $subpackage["merchantpack_name"]?>
						(<?php echo SubscriptionHelper::displayFormattedSubPackage($subpackage['merchantsubpack_actual_price'] , $subpackage['merchantsubpack_subs_frequency'], $subpackage['merchantsubpack_subs_period'] ) ; ?>)</strong></a>
						</div>
                        <?php if ($subpackage['free_trial']):?>
                        <br/>
                        <span class="small">- <?=Utilities::getLabel('M_Free_Trial')?></span>
                        <?php endif; ?>
						</td>
						<td nowrap="nowrap"><div class="price"><?php echo Utilities::displayMoneyFormat($subpackage['merchantsubpack_actual_price']); ?></div></td>
					</tr>
                  <?php } ?> 
                </tbody>
              </table>
				