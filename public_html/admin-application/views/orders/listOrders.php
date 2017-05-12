<?php global $payment_status_arr; global $button_status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="15%">INV No.</th>
        <th width="15%">Customer</th>
        <th width="15%">Date</th>
        <th width="15%">Total</th>
        <th width="20%">Payment Status</th>
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
                <td><?php echo $i; ?></td>
                <td><?php echo $record["order_invoice_number"] ?></td>
                <td><?php echo $record["order_user_name"]?></td>
                <td><?php echo Utilities::formatDate($record["order_date_added"],true) ?></td>
                <td><?php echo $currencyObj->format($record["order_net_charged"],$record['order_currency_code'],$record['order_currency_value']) ?></td>
                <td><span class="label <?php echo $button_status_arr[Utilities::displayNotApplicable($payment_status_arr[$record["order_payment_status"]])]; ?>"><?php echo Utilities::displayNotApplicable($payment_status_arr[$record["order_payment_status"]])?></span></td>
                <?php if ($canview === true) { ?>
                    
                    <td>
                        <ul class="actions">
                        	<li><a href="<?php echo Utilities::generateUrl('orders', 'view', array($record['order_id']))?>" title="View Order"><i class="ion-eye icon"></i></a></li>
                            <li><a href="<?php echo Utilities::generateUrl('vendororders', 'default_action')?>?order=<?php echo $record['order_id']?>" title="View Vendor Orders"><i class="ion-search icon"></i></a></li>
							<?php if (!$record["order_payment_status"]) {?>
                            <li><a onclick="ConfirmOrderCancel('<?php echo $record['order_id'];?>', $(this));"  title="Cancel" href="javascript:void(0);"><i class="ion-android-cancel icon"></i></a></li>
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