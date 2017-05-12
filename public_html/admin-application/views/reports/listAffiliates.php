<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Date</th>
      <th>Account Balance</th>
      <th>Revenue</th>
      <th>Commission Received</th>
      <th>Commission Pending</th>
      <th>Signups</th>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        foreach ($records as $record) { ?>
            <tr>
                <td><?php echo $record["affiliate_name"]?></td>
                <td><?php echo $record["affiliate_email"]?></td>
                <td><?php echo Utilities::formatDate($record["affiliate_added_on"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["balance"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["revenue"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["received"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["pending"])?></td>
                <td><?php echo ($record["signups"])?></td>
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