<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<div class="sectionhead"><h4>View - Promotion Clicks</h4> <ul class="actions">
        <li class="droplink">
            <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
            <div class="dropwrap">
                <ul class="linksvertical">
                   <li><a href="#" onclick="loadPromotions();">Back to Promotions</a></li>	
                </ul>
            </div>
        </li>
    </ul></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th>Click ID</th>											 
        <th>User</th>
        <th>IP</th>
        <th>Date</th>
        <th>Cost</th>
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
                <td><?php echo $record["pclick_id"] ?></td>						
                <td><?php echo Utilities::displayNotApplicable($record["user_name"]) ?></td>
                <td><?php echo $record["pclick_ip"] ?></td>
                <td><?php echo displayDate($record["pclick_datetime"]) ?></td>
                <td><?php echo Utilities::displayMoneyFormat($record["pclick_cost"]) ?></td>
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
