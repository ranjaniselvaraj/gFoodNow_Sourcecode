<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="nowrap" >INV No.</th>
        <th width="nowrap">Vendor</th>
        <th width="nowrap">Customer</th>
        <th width="nowrap">Date</th>
        <th width="nowrap">Amount</th>
        <th width="nowrap">Status</th>
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
                <td><?php echo $record["opr_order_invoice_number"] ?></td>
                <td><strong>U</strong>: <?php echo $record["opr_shop_owner_username"] ?><br/>
                <strong>E</strong>: <a href="mailto:<?php echo $record["opr_shop_owner_email"]?>"><?php echo $record["opr_shop_owner_email"] ?></a><br/>
                <strong>P</strong>: <?php echo $record["opr_shop_owner_phone"] ?></td>
                <td><strong>U</strong>: <?php echo $record["user_username"] ?><br/>
                <strong>E</strong>: <a href="mailto:<?php echo $record["order_user_email"]?>"><?php echo $record["order_user_email"] ?></a><br/>
                <strong>P</strong>: <?php echo $record["order_user_phone"] ?></td>    
                <td><?php echo Utilities::formatDate($record["order_date_added"]) ?></td>
                <td> <strong><?php echo $currencyObj->format($record["opr_net_charged"],$record['order_currency_code'],$record['order_currency_value']) ?></strong></td>
                <td nowrap="nowrap"><?php echo $record["orders_status_name"] ?></td>
                <?php if ($canview === true) { ?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
              
			   <li><a title = "View Order"  href = "' . Utilities::generateUrl('vendororders', 'view', array($record['opr_id'])) . '" ><i class ="ion-eye icon"></i></a></li>
               <li><a title = "Cancel" href = "' . Utilities::generateUrl('vendororders', 'cancel_order', array($record['opr_id'])) . '" ><i class ="ion-close-circled icon"></i></a></li>
				</ul>';
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