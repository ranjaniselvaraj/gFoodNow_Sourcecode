<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="25%">Title</th>
      <th width="10%" >Unit Price</th>
      <th nowrap="nowrap" >No. of Orders</th>
      <th width="8%">Sold Qty</th>
      <th width="10%">Total (A)</th>
      <th width="10%">Shipping (B)</th>
      <th width="10%">Tax (C)</th>
      <th width="10%">Total (A+B+C)</th>
      <th width="10%" nowrap="nowrap">Commisssion</th>
  </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        foreach ($records as $record) { ?>
            <tr>
                <td><?php echo $record["prod_name"]?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["prod_sale_price"])?></td>
                <td align="center"><?php echo is_null($record["totOrders"])?0:$record["totOrders"]?></td>
                <td align="center"><?php echo is_null($record["totSoldQty"])?0:$record["totSoldQty"]?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["total"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["shipping"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["tax"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["sub_total"])?></td>
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