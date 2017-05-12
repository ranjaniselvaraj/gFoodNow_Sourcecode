<?php global $conf_arr_buyer_seller_advertiser_types; global $user_status;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="15%">Name</th>
      <th width="20%">Username/Email</th>
      <th width="12%">Added On</th>
      <th width="13%">Status</th>
      <th nowrap="nowrap">Email Verified</th>
      <th width="10%">Balance</th>
        <?php if ($canviewcustomers === true) {
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
            <tr style="color:<?php if (($record['user_status']==0)){ ?>#AAAAAA<?php }?>">
                <td><?php echo $record["user_name"]?></td>
                <td><strong>U</strong>: <?php echo $record["user_username"]?><br/><strong>E</strong>: <?php echo $record["user_email"]?></td>
                <td nowrap="nowrap"><?php echo Utilities::formatDate($record['user_added_on']); ?></td>
                <td><?php echo $user_status[$record['user_status']]; ?></td>
                <td><?php echo $record['user_email_verified']==1?"<span class='label label-success'>Verified</span>":"<span class='label label-danger'>Not Verified</span>"; ?></td>
                <td><a href="<?php echo Utilities::generateUrl('users', 'customer_form', array($record['user_id'],'transactions'))?>"><?php echo Utilities::displayMoneyFormat($record['totUserBalance']); ?></a></td>
                
                <?php if ($canviewcustomers === true) { 
						//$css=$record['user_status']==1?'ion-close-circled':'ion-checkmark-circled';
						$hover_text = 'Click to Disable';
						$css_anchor_text = 'enabled';
						if ($record['user_status']==0){
							$hover_text = 'Click to Enable';
							$css_anchor_text = 'disabled';
						}
					?>
                    
                    <td nowrap="nowrap">
                        <?php
                        echo '<ul class = "actions">
               <li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateAdvertiserStatus(' . $record['user_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Login into Store" target="_blank"  href = "' . Utilities::generateUrl('users', 'login', array($record['user_id'])) . '" ><i class ="ion-log-in icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('users', 'customer_form', array($record['user_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmAdvertiserDelete(' . $record['user_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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