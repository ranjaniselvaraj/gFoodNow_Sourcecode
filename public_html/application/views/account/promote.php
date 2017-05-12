<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $duration_freq_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_My_Promotions')?></h3>
          
          <?php if (!$is_advertiser_logged) {?>
          	<ul class="arrowTabs">
                <li><a href="<?php echo Utilities::generateUrl('account', 'promote_product')?>"><?php echo Utilities::getLabel('L_Promote_Product')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('account', 'promote_shop')?>"><?php echo Utilities::getLabel('L_Promote_Shop')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('account', 'promote_banner')?>"><?php echo Utilities::getLabel('L_Promote_Banner')?></a></li>
          	</ul>
          
          <?php } else { ?>
          	<ul class="arrowTabs">
                  <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'promote')?>"><?php echo Utilities::getLabel('L_Promotions_List')?></a></li>
                  <li><a href="<?php echo Utilities::generateUrl('account', 'promote_banner')?>"><?php echo Utilities::getLabel('L_Add_Promotion')?></a></li>
                </ul>
          <?php } ?>
          <div class="clearfix"></div>
          <?php if (!empty($promotions) && is_array($promotions)):?>
          <div class="darkgray-form clearfix">
            <div class="left-txt"> <?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?> </div>
          </div>
          <div class="tbl-listing">
            <table>
              <tr>
                <th></th>
                <th>ID</th>
                <th width="25%"><?php echo Utilities::getLabel('L_Name')?></th>
                <th><?php echo Utilities::getLabel('L_Type')?></th>
                <th><?php echo Utilities::getLabel('L_CPC')?></th>
                <th width="17%"><?php echo Utilities::getLabel('L_Budget')?></th>
                <th><?php echo Utilities::getLabel('L_Clicks')?></th>
                <th width="24%"><?php echo Utilities::getLabel('L_Duration')?></th>
                <th width="10%"><?php echo Utilities::getLabel('L_Approved')?></th>
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
                
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_ID')?></span> 
					<?php echo $row["promotion_number"] ?>
                </td>
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
               
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Type')?></span> <?php if ($row['promotion_type']==1) { 
					echo Utilities::getLabel('L_Product');
				} elseif ($row['promotion_type']==2) {
				   echo Utilities::getLabel('L_Shop');
			    } elseif ($row['promotion_type']==3) {
					echo Utilities::getLabel('L_Banner');
				} ?></td>
                
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_CPC')?></span> <?php echo Utilities::displayMoneyFormat($row["promotion_cost"]) ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Budget')?></span> <?php echo Utilities::displayMoneyFormat($row["promotion_budget"]) ?> / <?php echo $duration_freq_arr[$row["promotion_budget_period"]]?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Clicks')?></span> <a href="<?php echo Utilities::generateUrl('account', 'promotion_clicks', array($row['promotion_id']))?>" title="<?php echo Utilities::getLabel('L_Clicks')?>"><?php echo $row["totClicks"] ?></a></td>
                
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Duration')?></span> <?php echo Utilities::formatDate($row["promotion_start_date"]) ?> - <?php echo Utilities::formatDate($row["promotion_end_date"]) ?><br/><?php echo Utilities::getLabel('L_Time')?>: <? echo date(date('H:i',strtotime($row["promotion_start_time"]))) ?> - <? echo date(date('H:i',strtotime($row["promotion_end_time"]))) ?></td>
                <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Is_Approved')?></span> <?php echo $row["promotion_is_approved"]?Utilities::getLabel('L_Yes'):Utilities::getLabel('L_No'); ?></td>
               
                <td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Action')?></span>
                <a href="<?php echo Utilities::generateUrl('account', 'promote_form', array($row['promotion_id']))?>" title="<?php echo Utilities::getLabel('L_Edit')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/tag.svg" alt=""></a>
                <?php if ($row['promotion_status']==0):?>
					<a href="<?php echo Utilities::generateUrl('account', 'promotion_status', array($row['promotion_id'], 'unblock'))?>" title="<?php echo Utilities::getLabel('L_Enable')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/bulb01.svg" alt=""></a>
				<?php  else : ?>
                    <a href="<?php echo Utilities::generateUrl('account', 'promotion_status', array($row['promotion_id'], 'block'))?>" title="<?php echo Utilities::getLabel('L_Pause')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/bulb02.svg" alt=""></a>
				<?php endif; ?>
                <a href="<?php echo Utilities::generateUrl('account', 'promotion_analytics', array($row['promotion_id']))?>" title="<?php echo Utilities::getLabel('L_Analytics')?>" class="actions"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/list.svg" alt=""></a>
                
               </td>
              </tr> 
              <?php endforeach;?>
            </table>
            <?php if ($pages>1):?>
            <div class="pager">
              <ul>
              <?php echo getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx);">xxpagexx</a></li>', $pages, $page,'<li class="active"><a  href="javascript:void(0)">xxpagexx</a></li>', '<li>...</li>');?>
              </ul>
            </div>
            <?php endif;?>
          </div>
		  <?php else:?>    
	        	<div class="space-lft-right">
                	<div class="alert alert-info">
    	    			<?php echo Utilities::getLabel('L_You_have_not_added_promotion')?>
		        	</div>
                </div>
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
