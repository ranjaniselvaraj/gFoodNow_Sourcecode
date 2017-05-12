<?php global $supplier_request_status; 
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
    	<th width="6%">S. No.</th>
        <th width="25%">Request By</th>
        <th width="25%">Requested On</th>
        <th width="20%">Brand Name</th>
        <th width="15%">Status</th>
        <?php if ($canviewsupprequests === true) {
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
                <td><strong>U</strong>: <?php echo $record["user_username"]?><br/><strong>E</strong>: <?php echo $record["user_email"]?></td>
                <td><?php echo $record['urequest_date']; ?></td>
                <td><?php echo $record['urequest_text']; ?></td>
                <td><?php echo $supplier_request_status[$record['urequest_status']]; ?></td>
                <?php if ($canviewsupprequests === true) { 
						$css=$record['user_status']==1?'ion-close-circled':'ion-checkmark-circled';
					?>
                    
                    <td nowrap="nowrap">
                        <?php
						if ($record['urequest_status']==0){
                       	 	echo '<ul class = "actions">
               					<li><a href="javascript:void(0);" title="Approve" class="toggleswitch enabled" ><i onclick="UpdateRequestStatus(' . $record['urequest_id'] . ', $(this),\'approve\');" class="ion-checkmark-circled icon"></i></a></li>
								<li><a href="javascript:void(0);" title="Decline" class="toggleswitch disabled" ><i onclick="UpdateRequestStatus(' . $record['urequest_id'] . ', $(this),\'decline\');" class="ion-close-circled icon"></i></a></li>
							</ul>';
						}
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