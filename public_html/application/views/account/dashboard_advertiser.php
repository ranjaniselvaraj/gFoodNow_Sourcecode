<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $duration_freq_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="tabz-content">
              <h3><?php echo Utilities::getLabel('M_Your_Account_Summary')?></h3>
              <div class="mycredits space-lft-right">
                <div class="box">
                  <div class="crr-blnc"><?php echo Utilities::getLabel('L_Current_Balance')?>: <strong><?php echo Utilities::displayMoneyFormat($user_details["totUserBalance"])?></strong> </div>
                  <a class="btn" href="<?php echo Utilities::generateUrl('account','request_withdrawal')?>"><?php echo Utilities::getLabel('L_Request_Withdrawal')?></a> </div>
                <div class="box last">
	                <?php echo str_replace("<br>",'',$walletfrm->getFormHtml()); ?>
    	         </div>
	         </div>
               
              <div class="gap"></div>
              <div class="tbl-dashboard">
                  <h3><?php echo sprintf(Utilities::getLabel('L_Latest_x_Promotions'),5)?></h3>
                  <?php if (count($promotions)>0 && (!empty($promotions))):?>	
                  <div class="tbl-listing">
           			 <table>
              			<tr>
		                <th></th>
        		        <th><?php echo Utilities::getLabel('L_ID')?></th>
                		<th width="25%"><?php echo Utilities::getLabel('L_Name')?></th>
		                <th><?php echo Utilities::getLabel('L_CPC')?></th>
        		        <th width="17%"><?php echo Utilities::getLabel('L_Budget')?></th>
                		<th><?php echo Utilities::getLabel('L_Clicks')?></th>
		                <th width="24%"><?php echo Utilities::getLabel('L_Duration')?></th>
        		        <th><?php echo Utilities::getLabel('L_Actions')?></th>
              		</tr>
	    	        <?php $cnt=0; foreach ($promotions as $sn=>$row): $sn++;  ?>
    	    	    <tr>
                	<td><div class="pro-image">
                	<?php if ($row['promotion_type']==1) {?>
                	<img src="<?php echo Utilities::generateUrl('image','product_image',array($row["promotion_product_id"],'THUMB'))?>" alt="<?php echo $row["prod_name"]?>"/>
                	<?php } elseif ($row['promotion_type']==2) {?>
                	<img src="<?php echo Utilities::generateUrl('image','shop_logo',array($row["shop_logo"],'THUMB'))?>" alt="<?php echo $row["shop_name"]?>"/>
            	    <?php } elseif ($row['promotion_type']==3) {?>
        	        <img src="<?php echo Utilities::generateUrl('image','promotion_banner',array($row["promotion_banner_file"],'THUMB'))?>" alt="<?php echo $row["shop_name"]?>"/>
    	            <?php } ?>
	                </div></td>
                    <td><span class="cellcaption"><?php echo Utilities::getLabel('L_ID')?></span> <?php echo $row["promotion_number"] ?></td>
                    <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Name')?></span> 
				
					<?php 
					if ($row['promotion_type']==1) { 
						echo $row["prod_name"];
					} elseif ($row['promotion_type']==2) {
					   echo $row["shop_name"];
					} elseif ($row['promotion_type']==3) {
						echo $row["promotion_banner_name"];
					}
					?><?php if ($row['promotion_min_balance']==1) { ?><br/><span class="text-danger">***</span><?php } ?>
                	</td>
                	<td><span class="cellcaption"><?php echo Utilities::getLabel('L_CPC')?></span> <?php echo Utilities::displayMoneyFormat($row["promotion_cost"]) ?></td>
                	<td><span class="cellcaption"><?php echo Utilities::getLabel('L_Budget')?></span> <?php echo Utilities::displayMoneyFormat($row["promotion_budget"]) ?> / <?php echo $duration_freq_arr[$row["promotion_budget_period"]]?></td>
                	<td><span class="cellcaption"><?php echo Utilities::getLabel('L_Clicks')?></span> <a href="<?php echo Utilities::generateUrl('account', 'promotion_clicks', array($row['promotion_id']))?>" title="<?php echo Utilities::getLabel('L_Clicks')?>"><?php echo $row["totClicks"] ?></a></td>
                
                	<td><span class="cellcaption"><?php echo Utilities::getLabel('L_Duration')?></span> <?php echo Utilities::formatDate($row["promotion_start_date"]) ?> - <?php echo Utilities::formatDate($row["promotion_end_date"]) ?><br/><?php echo Utilities::getLabel('L_Time')?>: <? echo date(date('H:i',strtotime($row["promotion_start_time"]))) ?> - <? echo date(date('H:i',strtotime($row["promotion_end_time"]))) ?></td>
               
                	<td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Action')?></span>
        		        <a href="<?php echo Utilities::generateUrl('account', 'promote_form', array($row['promotion_id']))?>" title="<?php echo Utilities::getLabel('L_Edit')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/tag.svg" alt=""></a>
		                <?php if ($row['promotion_status']==0):?>
						<a href="<?php echo Utilities::generateUrl('account', 'promotion_status', array($row['promotion_id'], 'unblock'))?>" title="<?php echo Utilities::getLabel('L_Enable')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/bulb01.svg" alt=""></a>
						<?php  else : ?>
                	    <a href="<?php echo Utilities::generateUrl('account', 'promotion_status', array($row['promotion_id'], 'block'))?>" title="<?php echo Utilities::getLabel('L_Disable')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/bulb02.svg" alt=""></a>
						<?php endif; ?>
		                <a href="<?php echo Utilities::generateUrl('account', 'promotion_analytics', array($row['promotion_id']))?>" title="<?php echo Utilities::getLabel('L_Analytics')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/list.svg" alt=""></a>
    	    	       </td>
	    	          </tr> 
            	  	<?php endforeach;?>
        	    	</table>
		          </div>
                  <?php else:?>
                  <div class="space-lft-right">
                      <div class="alert alert-info">
                            <?php echo Utilities::getLabel('L_You_have_not_added_promotion')?>
                      </div>
                  </div> 
                <?php endif;?>
                  <div class="gap"></div>
                
                
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
