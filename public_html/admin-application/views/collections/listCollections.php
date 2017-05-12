<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="60%">Title</th>
		<th width="20%">Type</th>
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
            <tr style="color:<?php if (($record['collection_status']==0)){ ?>#AAAAAA<?php }?>">
                <td><?php echo $i; ?></td>
                <td><?php echo $record["collection_name"]?></td>
				<td><?php echo $collection_types[$record["collection_type"]]?>
                <?php if ($canview === true) { 
						//$css=$record['collection_status']==1?'ion-close-circled':'ion-checkmark-circled';
						$hover_text = 'Click to Disable';
						$css_anchor_text = 'enabled';
						if ($record['collection_status']==0){
							$hover_text = 'Click to Enable';
							$css_anchor_text = 'disabled';
						}
					?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
               <li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateCollectionStatus(' . $record['collection_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('collections', 'form', array($record['collection_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmCollectionDelete(' . $record['collection_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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