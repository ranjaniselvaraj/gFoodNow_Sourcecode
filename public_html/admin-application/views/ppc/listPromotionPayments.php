<?php global $duration_freq_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<div class="sectionhead"><h4>View - Promotion Payments</h4> <ul class="actions">
        <li class="droplink">
            <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
            <div class="dropwrap">
                <ul class="linksvertical">
                   <li><a href="#" onclick="loadPromotions();">Back to Promotions</a></li>	
                </ul>
            </div>
        </li>
    </ul> </div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th>#</th>											 
        <th>User</th>
        <th>Clicks</th>
        <th>Date</th>
        <th>Charged Amount</th>
        <th>Click ID(s)</th>
        <th>Duration</th>
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
                <td><?php echo $record["pcharge_id"] ?></td>						
                <td><?php echo $record["user_name"] ?></td>
                <td><?php echo $record["pcharge_clicks"] ?></td>
                <td><?php echo displayDate($record["pcharge_date"]) ?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["pcharge_charged_amount"]) ?></td>
                <td>From <?php echo $record["pcharge_start_click_id"]?> to <?php echo $record["pcharge_end_click_id"]?></td>
                <td><?php echo displayDate($record["pcharge_start_date"],true) ?> to <?php echo displayDate($record["pcharge_end_date"],true) ?></td>
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
<script>
</script>		