<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<div class="sectionhead"><h4>View - Promotion Logs</h4> <ul class="actions">
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
        <th>Date</th>
        <th>Impressions</th>
        <th>Clicks</th>
        <th>Orders</th>
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
                <td><?php echo Utilities::formatDate($record["lprom_date"]) ?></td>
                <td><?php echo $record["lprom_impressions"] ?></td>
                <td><?php echo $record["lprom_clicks"] ?></td>
                <td><?php echo $record["lprom_orders"] ?></td></tr>
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