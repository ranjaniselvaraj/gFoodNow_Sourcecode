<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="12%">Invoice</th>
      <th width="12%">Subscriber</th>
      <th width="12%">Subscription Date</th>
      <th width="15%">Plan</th>
      <th width="10%">Status</th>
      <th width="5%" nowrap="nowrap">No. of Payments</th>
      <th width="10%">Total</th>
	</tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        foreach ($records as $record) { ?>
            <tr>
                <td><?php echo $record["mporder_invoice_number"]?></td>
                <td><?php echo $record["mporder_user_name"]?></td>
                <td><?php echo Utilities::formatDate($record["mporder_date_added"],true)?></td>
                <td><?php echo $record['mporder_merchantpack_name'].' - '.$record['mporder_merchantsubpack_name']?></td>
                <td><?php echo $record['sorder_status_name'];?></td>
                <td><?php echo $record["totPaymentRecords"]?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["totPayments"])?></td>
            </tr>
            <?php
           
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