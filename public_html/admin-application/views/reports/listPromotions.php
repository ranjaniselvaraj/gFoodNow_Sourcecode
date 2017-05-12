<?php
global $duration_freq_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th width="10%">Promoter</th>
      <th width="22%">Title</th>
      <th width="6%">Type</th>
      <th width="4%" >CPC</th>
      <th width="12%">Budget</th>
      <th width="4%">Impressions</th>
      <th width="4%">Clicks</th>
      <th width="5%">Payments</th>
      <th width="18%">Duration</th>
      <th>Approved</th>
  </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        foreach ($records as $record) { ?>
            <tr>
                <td><?php echo $record['user_name'] ?></td>
                <td><?php 
                        if ($record['promotion_type']==1) { 
                        echo $record["prod_name"];
                        } elseif ($record['promotion_type']==2) {
                        echo $record["shop_name"];
                        } elseif ($record['promotion_type']==3) {
                        echo $record["promotion_banner_name"];
                        }
                        ?></td>
                <td><?php 
                        if ($record['promotion_type']==1) { 
                        echo 'Product';
                        } elseif ($record['promotion_type']==2) {
                        echo 'Shop';
                        } elseif ($record['promotion_type']==3) {
                        echo 'Banner';
                        }
                        ?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["promotion_cost"]) ?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["promotion_budget"]) ?> / <?php echo $duration_freq_arr[$record["promotion_budget_period"]]?></td>
                <td><?php echo $record["totImpressions"] ?></td>
                <td><?php echo $record["totClicks"] ?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["totPayments"]) ?></td>
                <td><?php echo Utilities::formatDate($record["promotion_start_date"]) ?> - <?php echo Utilities::formatDate($record["promotion_end_date"]) ?><br/>Time Slot: <? echo date(date('H:i',strtotime($record["promotion_start_time"]))) ?> - <? echo date(date('H:i',strtotime($record["promotion_end_time"]))) ?></td>
            	<td><?php echo $record["promotion_is_approved"]?'Yes':'No'; ?></td>
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