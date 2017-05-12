<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<div class="sectionhead"><h4>Manage - Navigations</h4></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="50%">Title</th>
		<th width="30%">Status</th>
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
            <tr style="color:<?php if (($record['nav_status']==0)){ ?>#AAAAAA<?php }?>">
                <td><?php echo $i; ?></td>
                <td><strong><?php echo $record["nav_name"]?></strong></td>
			    <td><?php echo $record['nav_status']==1?"<span class='label label-success'>Enabled</span>":"<span class='label label-danger'>Disabled</span>"; ?>
				</td>
                <?php if ($canview === true) { 
						$hover_text = 'Click to Disable';
						$css_anchor_text = 'enabled';
						if ($record['nav_status']==0){
							$hover_text = 'Click to Enable';
							$css_anchor_text = 'disabled';
						}
					?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
               <li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateNavigationStatus(' . $record['nav_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('navigations', 'form', array($record['nav_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Pages" href = "' . Utilities::generateUrl('navigations', 'display', array($record['nav_id'])) . '" ><i class ="ion-document-text icon"></i></a></li>
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