<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="65%">Name</th>
		<th width="15%">Display Order</th>
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
                <td><?php echo trim($record["filter_group_name"]) ?></td>
				<td><?php echo $record["filter_group_display_order"] ?></td>
                <?php if ($canview === true) { ?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('filters', 'form', array($record['filter_group_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmFilterDelete(' . $record['filter_group_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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