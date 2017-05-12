<?php global $status_arr;global $button_status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="8%">ID</th>
        <th width="15%">User Details</th>
        <th width="12%">Balance</th>
        <th width="12%">Amount</th>
        <th width="25%">Account Details</th>
        <th width="10%">Date</th>
        <th width="10%">Status</th>
        <?php if ($canviewwithdrawalrequests === true) {
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
            <tr>
                <td>#<?php echo str_pad($record["withdrawal_id"],6,'0',STR_PAD_LEFT);?></td>
                <td><strong>N</strong>:<?php echo $record["user_name"]?><br/><strong>U</strong>: <?php echo $record["user_username"]?><br/><strong>E</strong>: <?php echo $record["user_email"]?></td>
                <td><a href="<?php echo Utilities::generateUrl('users', 'customer_form', array($record['user_id'],'transactions'))?>"><?php echo Utilities::displayMoneyFormat($record["userBalance"])?></a></td>
                <td><?php echo Utilities::displayMoneyFormat($record["withdrawal_amount"])?></td>
                <td><strong>Bank Name:</strong> <?php echo trim($record["withdrawal_bank"])?><br/><strong>A/c Name</strong>: <?php echo $record['withdrawal_account_holder_name']?><br/><strong>A/c Number</strong>: <?php echo $record['withdrawal_account_number']?><br/><strong>IFSC Code/Swift Code</strong>: <?php echo $record['withdrawal_ifc_swift_code']?><br/><strong>Bank Address</strong>: <?php echo $record['withdrawal_bank_address']?><br/><strong>Comments/Instructions</strong>: <?php echo $record['withdrawal_comments']?><?php if (strlen($record['withdrawal_cancel_comments'])):?><br/><strong>Reason for cancellation</strong>: <?php echo $record['withdrawal_cancel_comments']?><?php endif;?></td>
                <td><?php echo Utilities::formatDate($record["withdrawal_request_date"])?></td>
                <td>
                <?php $labelInfo=$button_status_arr[$status_arr[$record["withdrawal_status"]]];?>
                <span class="label <?php echo ($labelInfo!='')?$labelInfo:'label-info'; ?>">												
                <?php echo $status_arr[$record["withdrawal_status"]]?></span>
                </td>
                <?php if ($canviewwithdrawalrequests === true) { ?>
                    
                    <td nowrap="nowrap">
                        <?php
						if ($record['withdrawal_status']==0){
                       	 	echo '<ul class = "actions">
               					<li><a href="javascript:void(0);" title="Approve" class="toggleswitch enabled" ><i onclick="UpdateRequestStatus(' . $record['withdrawal_id'] . ', $(this),\'approve\');" class="ion-checkmark-circled icon"></i></a></li>
								<li><a href="' . Utilities::generateUrl('custom', 'cancel_withdrawal_request', array($record['withdrawal_id'])) . '" title="Decline" class="toggleswitch disabled" ><i class="ion-close-circled icon"></i></a></li>
							</ul>';
						}
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