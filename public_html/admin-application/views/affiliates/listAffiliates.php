<?php global $user_status;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="6%">S. No.</th>
      <th width="15%">Name</th>
      <th width="20%">Username/Email</th>
      <th width="10%">Balance</th>
      <th width="10%">Added On</th>
      <th width="8%">Status</th>
      <th width="5%">Signups</th>
      <th width="5%">Orders</th>
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
            <tr style="color:<?php if (($record['affiliate_status']==0)){ ?>#AAAAAA<?php }?>">
                <td><?php echo $i; ?></td>
                <td><?php echo $record["affiliate_name"]?></td>
				<td><strong>U</strong>: <?php echo $record["affiliate_username"]?><br/><strong>E</strong>: <?php echo $record["affiliate_email"]?></td>
                <td><a href="<?php echo Utilities::generateUrl('affiliates', 'form', array($record['affiliate_id'],'transactions'))?>"><?php echo Utilities::displayMoneyFormat($record['balance']); ?></a></td>
                <td nowrap="nowrap"><?php echo Utilities::formatDate($record['affiliate_added_on']); ?></td>
                <td><?php echo $user_status[$record['affiliate_status']]; ?></td>
                <td><?php echo ($record['signups']); ?></td>
                <td><?php echo ($record['orders']); ?></td>
            <?php if ($canview === true) { 
						$hover_text = 'Click to Disable';
						$css_anchor_text = 'enabled';
						if ($record['affiliate_status']==0){
							$hover_text = 'Click to Enable';
							$css_anchor_text = 'disabled';
						}
						$affiliate_approved = $record['affiliate_is_approved']==1?'<a title="Approved" class="toggleswitch deactivated"><i class="ion-thumbsup icon"></i></a>':'<li><a href="javascript:void(0);" title="Approve" class="toggleswitch actives" ><i onclick="ApproveAffiliate(' . $record['affiliate_id'] . ', $(this));" class="ion-checkmark-round icon"></i></a></li>';
					?>
                    
                    <td nowrap="nowrap">
                        <?php
                        echo '<ul class = "actions"><li>'.$affiliate_approved.'<li>&nbsp;<li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateAffiliateStatus(' . $record['affiliate_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Login into Store" target="_blank"  href = "' . Utilities::generateUrl('affiliates', 'login', array($record['affiliate_id'])) . '" ><i class ="ion-log-in icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('affiliates', 'form', array($record['affiliate_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmAffiliateDelete(' . $record['affiliate_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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