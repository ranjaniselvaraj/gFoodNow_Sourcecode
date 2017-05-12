<?php global $supplier_approval_request_status;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="15%">Reference Number</th>
        <th width="20%">Name</th>
        <th width="20%">Username/Email</th>
        <th width="20%">Requested On</th>
        <th width="20%">Status</th>
        <?php if ($canviewcancellationrequests === true) {
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
                <td><?php echo $record["usuprequest_reference"]?></td>
                <td><?php echo $record["user_name"]?></td>
                <td><strong>U</strong>: <?php echo $record["user_username"]?><br/><strong>E</strong>: <?php echo $record["user_email"]?></td>
                <td><?php echo $record['usuprequest_date']; ?></td>
                <td><?php echo $supplier_approval_request_status[$record['usuprequest_status']]; ?></td>
                <?php if ($canviewsuppapprequests === true) { ?>
                   <td nowrap="nowrap">
                        <?php
						
                       	 	echo '<ul class = "actions">
               					<li><a href="'.Utilities::generateUrl('users', 'view_request', array($record['usuprequest_id'])).'" title="View Request" class="toggleswitch actives" ><i class="ion-eye icon"></i></a></li>
						
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