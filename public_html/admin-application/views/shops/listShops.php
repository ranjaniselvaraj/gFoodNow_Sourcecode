<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th >Shop Owner</th>
        <th >Name</th>
        <th >Items</th>
        <th >Reviews</th>
        <th >Reports</th>
        <th >Active</th>
        <th>Display Status</th>
        <?php if ($canview === true) {
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
            <tr style="color:<?php if (($record['shop_status']==0)){ ?>#AAAAAA<?php }?>">
                <td><?php echo $i; ?></td>
                <td><?php echo $record['shop_owner_username']; ?></td>
                 <td><?php echo $record['shop_name']; ?></td>
                <td><a href="<?php echo Utilities::generateUrl('products')?>?shop=<?php echo $record['shop_id']?>" title="Products"><?php echo $record["totProducts"]?></a></td>
                <td><a href="<?php echo Utilities::generateUrl('reviews')?>?shop_id=<?php echo $record['shop_id']?>" title="Reviews"><?php echo $record["totReviews"]?></a></td>
                <td><a href="<?php echo Utilities::generateUrl('shops','reports',array($record['shop_id']))?>" title="Shop Reports"><?php echo $record["totStoreReports"]?></a></td>
                <td><?php echo $record['shop_status']==1?'Yes':'No';?></td>
                <td align="center">
                    <?php if($record["shop_vendor_display_status"]==1):?>
                            <span class="label label-success">This Shop is Turned ON by Seller.</span>
                    <?php else:?>
                            <span class="label label-danger">This Shop is Turned OFF by Seller.</span>
                    <?php endif;?>
                </td>			
                    <?php if ($canview === true) { 
							$hover_text = 'Click to Disable';
							$css_anchor_text = 'enabled';
							if ($record['shop_status']==0){
								$hover_text = 'Click to Enable';
								$css_anchor_text = 'disabled';
							}
					?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
               <li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateShopStatus(' . $record['shop_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('shops', 'form', array($record['shop_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmShopDelete(' . $record['shop_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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