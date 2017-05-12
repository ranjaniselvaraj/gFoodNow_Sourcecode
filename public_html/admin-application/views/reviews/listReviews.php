<?php  global $review_status;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="30%">Product</th>
        <th width="15%">Shop</th>
        <th width="10%">Reviewed By</th>
        <th width="10%">Rating</th>
        <th width="10%">Date</th>
        <th width="10%">Status</th>
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
                <td><?php echo $record["prod_name"]?></td>
                <td><?php echo $record["shop_name"]?></td>
                <td><?php echo Utilities::displayNotApplicable($record["user_username"])?></td>
                <td nowrap="nowrap"><div class="ratings" data-score="<?php echo $record["review_rating"]?>" id="<?php echo $record["review_id"]?>"></div></td>
                <td><?php echo Utilities::formatDate($record['reviewed_on']);?></td>
                <td><form class="form-horizontal">
                    <?php echo createDropDownFromArray('review_status',$review_status,$record["review_status"],'onchange="javascript:saveReviewData('.$record["review_id"].',this);" class="auto"',''); ?>
                    </form>
                </td>
                <?php if ($canview === true) { ?>
                    <td>
                        <?php
                        echo '<ul class = "actions">
							   <li><a title = "View/Edit"  href = "' . Utilities::generateUrl('reviews', 'view', array($record['review_id'])) . '" ><i class ="ion-eye icon"></i></a></li>
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
