<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th>Shop Name</th>
      <th>Orders</th>
      <th>Sales</th>
      <th>Commission</th>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        foreach ($records as $record) { 
            ?>
            <tr>
                <td><?php echo $record["opr_product_shop_name"]?></td>
                <td><?php echo $record["totvendororders"]?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["sales"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["commission"])?></td>
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