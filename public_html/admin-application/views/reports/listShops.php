<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
 	 <tr>
          <th>Name</th>
          <th width="10%" >Shop Owner</th>
          <th width="8%">Items</th>
          <th width="8%">Sold Qty</th>
          <th width="10%">Sales</th>
          <th nowrap="nowrap">Site Commissions</th>
          <th>Reviews</th>
          <th >Rating</th>
      </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        foreach ($records as $record) {  ?>
            <tr>
                <td><a target="_blank" href="<?php echo Utilities::generateUrl('shops','form',array($record["shop_id"]))?>"><?php echo $record["shop_name"]?></a></td>
                <td><?php echo $record["shop_owner"]?></td>
                <td><?php echo $record["totProducts"]?></td>
                <td><?php echo $record["totSoldQty"]?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["sub_total"])?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["commission"])?></td>
                <td><?php echo $record["totReviews"]?></td>
                <td nowrap="nowrap"><div class="ratings" data-score="<?php echo $record["shop_rating"]?>" id="<?php echo $record["shop_id"]?>"></div></td>
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
