<?php global $txn_status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="6%">S. No.</th>
      <th width="10%">ID</th>
      <th width="10%">Date</th>
      <th width="10%">Credit</th>
      <th width="10%">Debit</th>
      <th width="10%">Balance</th>
      <th width="25%">Description</th>
      <th width="8%">Status</th>
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
                <td><?php echo $record["formatted_transaction_number"];?></td>		
                <td><?php echo Utilities::formatDate($record["utxn_date"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["utxn_credit"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["utxn_debit"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record['balance']); ?></td>
                <td><?php echo strip_tags(Utilities::renderHtml($record["formatted_comments"])) ?></td>
                <td><?php echo $txn_status_arr[$record["utxn_status"]]?></td>
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