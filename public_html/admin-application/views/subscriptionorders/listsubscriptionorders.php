<?php global $payment_status_arr; global $button_status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage'); 
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="13%">Invoice</th>
      <th width="nowrap">Vendor</th>
      <th width="nowrap">Date Added</th>
      <th width="nowrap">Subscription Plan</th>
      <th width="nowrap">Subscription Status</th>
      <th width="nowrap">Total</th>
      <th width="nowrap">Payment Status</th>
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
            <tr>
                <td><?php echo $record["mporder_invoice_number"] ?></td>
                <td>
                    <strong>U</strong>: <?=$record["mporder_user_name"] ?><br/>
                    <strong>E</strong>: <a href="mailto:<?=$record["mporder_user_email"]?>"><?=$record["mporder_user_email"] ?></a><br/>
                    <strong>P</strong>: <?=!empty($record["mporder_user_phone"]) ? $record["mporder_user_phone"] : 'NA' ?>
                 </td>
                <td><?=Utilities::formatDate($record["mporder_date_added"],true) ?></td>
                <td><?=$record['mporder_merchantpack_name'].' - <br/>'.$record['mporder_merchantsubpack_name']?></td>
                <td><?=$record['sorder_status_name'];?></td>
                <td><?=Utilities::displayMoneyFormat($record["mporder_merchantsubpack_subs_amount"]) ?></td>
                <td><span class="label <?=$button_status_arr[Utilities::displayNotApplicable($payment_status_arr[$record["mporder_payment_status"]])]; ?>"><?=Utilities::displayNotApplicable($payment_status_arr[$record["mporder_payment_status"]])?></span></td>
                	<?php if ($canview === true) { ?>
                    
                    <td nowrap="nowrap">
                        <ul class="actions">
                        	<li><a href="<?php echo Utilities::generateUrl('subscriptionorders', 'view', array($record['mporder_id']))?>" title="View Order"><i class="ion-eye icon"></i></a></li>
							<?php if (!$record["mporder_payment_status"]) {?>
                            <li><a onclick="ConfirmOrderCancel();"  title="Cancel" href="<?php echo Utilities::generateUrl('subscriptionorders', 'cancel_subscription', array($record['mporder_id']))?>"><i class="ion-android-cancel icon"></i></a></li>
                            <?php } ?>
                        </ul>
                      </td>
                    <?php } ?>  
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