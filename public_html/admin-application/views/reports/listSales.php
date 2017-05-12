<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
          <th>Date</th>
          <th>No. of Orders </th>
          <th>No. of Qty.</th>
          <th>Sub Total</th>
          <th>Tax</th>
          <th>Shipping</th>
          <th>Total</th>
          <th>Refunded Qty</th>
          <th>Refunded Amount</th>
          <th>Refunded Tax</th>
          <th>Sales Earnings</th>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
		$sn=0;
        foreach ($records as $record) { $sn++;
            ?>
            <tr>
                <td nowrap="nowrap"><a target="_blank" href="<?php echo Utilities::generateUrl('reports', 'salesdetails', array($record['order_date'],$record["order_currency_code"]))?>"><?php echo $record["order_date"]?></a></td>
                <td><?php echo $record["totOrders"]?></td>
                <td><?php echo $record["totQtys"]?></td>
                <td><?php echo $currencyObj->format($record["tot_cart_total"],$record["order_currency_code"],$record["order_currency_value"])?></td>
                <td><?php echo $currencyObj->format($record["tot_tax_charged"],$record["order_currency_code"],$record["order_currency_value"])?></td>
                <td><?php echo $currencyObj->format($record["tot_shipping"],$record["order_currency_code"],$record["order_currency_value"])?></td>
                <td><?php echo $currencyObj->format($record["tot_net_charged"],$record["order_currency_code"],$record["order_currency_value"])?></td>
                <td><?php echo $record["totRefundedQtys"]?></td>
                <td><?php echo $currencyObj->format($record["tot_refunded_amount"],$record["order_currency_code"],$record["order_currency_value"])?></td>
                <td><?php echo $currencyObj->format($record["tot_refunded_tax"],$record["order_currency_code"],$record["order_currency_value"])?></td>
                <td><?php echo $currencyObj->format($record["tot_sales_earnings"],$record["order_currency_code"],$record["order_currency_value"])?></td>
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