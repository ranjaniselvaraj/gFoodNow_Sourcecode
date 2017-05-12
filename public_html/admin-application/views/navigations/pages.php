<?php global $nav_page_type;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<div class="sectionhead"><h4>Manage - Navigation Pages</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('navigations', 'addeditnavigationPage',array($navigation['nav_id'])); ?>">Add Navigation Page</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            <div class="clear"></div>
   			   
                
						</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="50%">Title</th>
		<th width="30%">Type</th>
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
        $i = 1;
        foreach ($records as $record) {
            ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $record["nl_caption"]?></td>
			    <td><?php echo $nav_page_type[$record['nl_type']] ?></td>
                <?php if ($canview === true) { ?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
               
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('navigations', 'addEditNavigationPage', array($record['nl_nav_id'],$record['nl_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmNavigationPageDelete(' . $record['nl_id'] . ','.$record['nl_nav_id'].' );" ><i class ="ion-android-delete icon"></i></a></li>
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
    
}
?>
