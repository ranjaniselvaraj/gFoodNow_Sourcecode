<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<form class="web_form">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>											 
      <th width="20%">Name</th>
      <th width="10%">Visitor</th>
      <th width="10%">Date</th>
      <th width="5%">Time</th>
      <th width="5%">Visits</th>
      <th width="5%">Ordered</th>
      <th width="5%">Cancelled</th>
      <th width="5%">Favorite</th>
      <th width="5%">Wishlist</th>
      <th width="5%" nowrap="nowrap">In Cart</th>
      <!--<th width="5%">Removed Cart</th>-->
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
                    <td><?php echo $record["prod_name"] ?></td>
                    <td><?php echo Utilities::displayNotApplicable($record["visitor_name"]) ?></td>
                    <td><?php echo displayDate($record["pbhistory_datetime"],true) ?></td>
                    <td><?php echo $record["pbhistory_seconds_spent"] ?> secs</td>
                    <td><?php echo $record["pbhistory_visits_count"] ?></td>
                    <td><?php echo $record["pbhistory_is_ordered"]?"Y":"N" ?></td>
                    <td><?php echo $record["pbhistory_is_order_cancelled"]?"Y":"N" ?></td>
                    <td><?php echo $record["pbhistory_is_favorite"]?"Y":"N" ?></td>
                    <td><?php echo $record["pbhistory_is_included_in_wishlist"]?"Y":"N" ?></td>
                    <td><?php echo $record["pbhistory_is_in_cart"]?"Y":"N" ?></td>
                    <!--<td><?php echo $record["pbhistory_is_removed_from_cart"]?"Y":"N" ?></td>-->
            </tr>
            <?php
            $i++;
        }
        ?>
    </table></form>
    <div class="gap"></div>
    	<?php 
	    if ($pages > 1) {
        	$vars = array('page' => $page, 'pages' => $pages, 'start_record' => $start_record, 'end_record' => $end_record, 'total_records' => $total_records);
        	echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php', $vars);
    	}
	}
 ?>    
