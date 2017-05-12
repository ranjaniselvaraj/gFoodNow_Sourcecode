<?php global $duration_freq_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<div class="sectionhead"><h4>Manage - Promotions</h4></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="7%">Image</th>
        <th>ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>CPC</th>
        <th>Budget</th>
        <th>Impressions</th>
        <th>Clicks</th>
        <th>Payments</th>
        <th>Duration</th>
        <th>Is Approved</th>
        <?php if ($canviewpromotions === true) {
            echo '<th>Action</th>';
        } ?>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        $i = $start_record;
        foreach ($records as $record) {
            ?>
            <tr style="color:<?php if (($record['promotion_status']==0)){ ?>#AAAAAA<?php }?>">
                <td>
					<?php if ($record['promotion_type']==1) {?>
                    	<img src="<?php echo Utilities::generateUrl('image','product_image',array($record["promotion_product_id"],'MINI'),CONF_WEBROOT_URL)?>" alt="<?php echo $record["prod_name"]?>"/>
                    <?php } elseif ($record['promotion_type']==2) {?>
	                    <img src="<?php echo Utilities::generateUrl('image','shop_logo',array($record["shop_logo"],'MINI'),CONF_WEBROOT_URL)?>" alt="<?php echo $record["shop_name"]?>"/>
    	            <?php } elseif ($record['promotion_type']==3) {?>
                    <img src="<?php echo Utilities::generateUrl('image','promotion_banner',array($record["promotion_banner_file"],'THUMB'),CONF_WEBROOT_URL)?>" alt="<?php echo $record["shop_name"]?>"/>
                    <?php } ?>
                    
                    </td>
        	        <td><?php echo $record["promotion_number"]; ?></td>    
                    <td>
						<?php 
                        if ($record['promotion_type']==1) { 
                        echo $record["prod_name"];
                        } elseif ($record['promotion_type']==2) {
                        echo $record["shop_name"];
                        } elseif ($record['promotion_type']==3) {
                        echo $record["promotion_banner_name"];
                        }
                        ?>
                    </td>
                    <td><?php 
                        if ($record['promotion_type']==1) { 
                        echo 'Product';
                        } elseif ($record['promotion_type']==2) {
                        echo 'Shop';
                        } elseif ($record['promotion_type']==3) {
                        echo 'Banner';
                        }
                        ?></td>
                    <td><?php echo Utilities::displayMoneyFormat($record["promotion_cost"]) ?></td>
                    <td><?php echo Utilities::displayMoneyFormat($record["promotion_budget"]) ?> / <?php echo $duration_freq_arr[$record["promotion_budget_period"]]?></td>
                    
                    <td><?php echo $record["totImpressions"] ?></td>
                    <td><a href="Javascript:ViewPromotionClicks('<?php echo $record['promotion_id']?>');" title="Clicks"><?php echo $record["totClicks"] ?></a></td>
                    
                    <td><a href="Javascript:ViewPromotionPayments('<?php echo $record['promotion_id']?>');"  title="Payments"><?php echo Utilities::displayMoneyFormat($record["totPayments"]) ?></a></td>
                    <td><?php echo Utilities::formatDate($record["promotion_start_date"]) ?> - <?php echo Utilities::formatDate($record["promotion_end_date"]) ?><br/>Time Slot: <? echo date(date('H:i',strtotime($record["promotion_start_time"]))) ?> - <? echo date(date('H:i',strtotime($record["promotion_end_time"]))) ?></td>
                	<td><?php echo $record["promotion_is_approved"]?'Yes':'No'; ?></td>
				<?php if ($canviewpromotions === true) { 
						$hover_text = 'Click to Disable';
						$css_anchor_text = 'enabled';
						if ($record['promotion_status']==0){
							$hover_text = 'Click to Enable';
							$css_anchor_text = 'disabled';
						}
						$promotion_approved = $record['promotion_is_approved']==1?'<li><a class="toggleswitch deactivated"><i class="ion-thumbsup icon"></i></a></li>':'<li><a href="javascript:void(0);" title="Approve" class="toggleswitch actives" ><i onclick="ApprovePromotion(' . $record['promotion_id'] . ', $(this));" class="ion-checkmark-round icon"></i></a></li>';
					?>
                    
                    <td nowrap="nowrap">
                        <?php
                        echo '<ul class = "actions">
               '.$promotion_approved.'&nbsp;<li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateStatus(' . $record['promotion_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('ppc', 'promotions_form', array($record['promotion_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "View Log" href="javascript:void(0);" onclick="ViewPromotionLog(' . $record['promotion_id'] . ');" ><i class ="ion-information icon"></i></a></li>
				</ul>';
                    }
                    ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table>
    <div class="gap"></div>
    <?php 
    if ($pages > 1) {
        $vars = array('page' => $page, 'pages' => $pages, 'start_record' => $start_record, 'end_record' => $end_record, 'total_records' => $total_records);
        echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php', $vars);
    }
}
?>