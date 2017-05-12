<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$cntInc = 0;
echo createHiddenFormFromPost('paginateForm', '', array(), array());
?>
<div class="sectionhead"><h4>Manage - Product Categories</h4>
	
    <ul class="actions">
        <li class="droplink">
            <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
            <div class="dropwrap">
                <ul class="linksvertical">
                   <li><a href="<?php echo Utilities::generateUrl('categories', 'form',array(0,$srch['parent'])); ?>">Add Category</a></li>	
                </ul>
            </div>
        </li>
    </ul>
    <div class="clear"></div>
    <ul class="breadcrumb ">
				<li><a href="javascript:void(0);" onclick="showChildCategories('0', $(this));">Home</a> </li>
			   <?php foreach($category_structure as $catKey=>$catVal): ?>			   
			   <?php  $cntInc++; if ($cntInc<count($category_structure)) :  ?>			 
					 <li><a href="javascript:void(0);" onclick="showChildCategories('<?php echo $catVal['category_id']?>', $(this));">
				<?php endif;?>
					 <li><?php echo $catVal["category_name"]?> </li>
					<?php if ($cntInc<count($category_structure)) :?> </a></li><?php endif;?>
				<?php endforeach;?>
				</ul>
                
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="10%">S. No.</th>
        <th width="30%">Name</th>
        <th width="20%">Parent</th>
        <th width="10%">Active</th>
        <th width="10%">Subcategory</th>
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
			$category_parent=$record["category_parent"]!=""?$record["category_parent"]:"-NA-";
            ?>
            <tr style="color:<?php if (($record['category_status']==0)){ ?>#AAAAAA<?php }?>">
                <td><?php echo $i; ?></td>
                <td><?php echo $record['category_name']; ?></td>
                <td><?php echo $category_parent; ?></td>
                <td><?php echo $record['category_status']==1?'Yes':'No';?></td>
                <td><a href="javascript:void(0);" onclick="showChildCategories('<?php echo $record['category_id']?>', $(this));"><?php echo $record["subcats"]?></a></td>
                <?php if ($canview === true) { 
							$hover_text = 'Click to Disable';
							$css_anchor_text = 'enabled';
							if ($record['category_status']==0){
								$hover_text = 'Click to Enable';
								$css_anchor_text = 'disabled';
							}
					?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
               <li><a href="javascript:void(0);" title="'.$hover_text.'" class="toggleswitch '.$css_anchor_text.'" ><i onclick="UpdateCategoryStatus(' . $record['category_id'] . ', $(this));" class="ion-checkmark-circled icon"></i></a></li>
			   <li><a title = "Edit"  href = "' . Utilities::generateUrl('categories', 'form', array($record['category_id'],$record['parent'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmCategoryDelete(' . $record['category_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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
