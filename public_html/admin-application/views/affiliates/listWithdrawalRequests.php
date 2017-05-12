<?php global $status_arr;global $button_status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="8%">ID</th>
        <th width="20%">Affiliate Details</th>
        <th width="12%">Amount</th>
        <th width="12%">Payment Mode</th>
        <th width="25%">Account Details</th>
        <th width="10%">Date</th>
        <th width="10%">Status</th>
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
			
			switch($record['afwithdrawal_payment_mode']) {
									        case 'bank':
            									$payment_details="<b>".Utilities::getLabel('L_Bank_Name')."</b>: ".$record["afwithdrawal_bank_name"]."<br/><b>".Utilities::getLabel('L_ABA/BSB_number_Branch_Number')."</b>: ".$record["afwithdrawal_bank_branch_number"]."<br/><b>".Utilities::getLabel('L_SWIFT_Code')."</b>: ".$record["afwithdrawal_bank_swift_code"]."<br/><b>".Utilities::getLabel('L_Account_Name').": ".$record["afwithdrawal_bank_account_name"]."</b><br/><b>".Utilities::getLabel('L_Account_Number')."</b>: ".$record["afwithdrawal_bank_account_number"];
											break;
						        			case 'paypal':
		        	    						$payment_details="<b>".Utilities::getLabel('L_PayPal_Email_Account')."</b>: ".$record["afwithdrawal_paypal"];
						        		    break;
											case 'cheque':
		        	    						$payment_details="<b>".Utilities::getLabel('L_Cheque_Payee_Name')."</b>: ".$record["afwithdrawal_cheque"];
						        		    break;
				}
				
            ?>
            <tr>
                <td>#<?php echo str_pad($record["afwithdrawal_id"],6,'0',STR_PAD_LEFT);?></td>
                <td><strong>N</strong>: <?php echo $record["affiliate_name"]?><br/><strong>U</strong>: <?php echo $record["affiliate_username"]?><br/><strong>E</strong>: <?php echo $record["affiliate_email"]?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["afwithdrawal_amount"])?></td>
                <td><?php echo ucfirst($record["afwithdrawal_payment_mode"])?></td>
                <td><?php echo $payment_details?><br/><strong>Comments/Instructions</strong>: <?php echo $record['afwithdrawal_comments']?></td>
                <td><?php echo Utilities::formatDate($record["afwithdrawal_request_date"])?></td>
                <td>
                <?php $labelInfo=$button_status_arr[$status_arr[$record["afwithdrawal_status"]]];?>
                <span class="label <?php echo ($labelInfo!='')?$labelInfo:'label-info'; ?>">												
                <?php echo $status_arr[$record["afwithdrawal_status"]]?></span>
                </td>
            <?php if ($canview === true) { 
						$css=$record['afwithdrawal_status']==1?'ion-close-circled':'ion-checkmark-circled';
			?>
                    
                    <td nowrap="nowrap">
                        <?php
                        if ($record['afwithdrawal_status']==0){
                       	 	echo '<ul class = "actions">
               					<li><a href="javascript:void(0);" title="Approve" class="toggleswitch enabled" ><i onclick="UpdateAffiliateWithdrawalRequestStatus(' . $record['afwithdrawal_id'] . ', $(this),\'approve\');" class="ion-checkmark-circled icon"></i></a></li>
								<li><a href="javascript:void(0);" title="Decline" class="toggleswitch disabled" ><i onclick="UpdateAffiliateWithdrawalRequestStatus(' . $record['afwithdrawal_id'] . ', $(this),\'decline\');" class="ion-close-circled icon"></i></a></li>
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