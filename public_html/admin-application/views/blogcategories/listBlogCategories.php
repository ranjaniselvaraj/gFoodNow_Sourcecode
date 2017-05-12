<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
?>
<?php
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table" id="category">
    <tr class="nodrag nodrop">       
        <th>Category Title</th>
    <!--    <th>Parent Category</th>
        <th>Category Display Order</th>-->
        <th>Category Status</th>
        <?php
        if ($canview === true) {
            echo '<th>Action</th>';
        }
        ?>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo ' <tr> <td colspan=5> No Record Found!! </td></tr>';
    }
    $i = $start_record;
    foreach ($records as $record) {
        ?>
        <tr id="<?php echo $record['category_id']; ?>" class="<?php echo ($record['category_status'] == 1) ? '' : 'inactive nodrag nodrop'; ?>">           
            <td><?php echo $record['category_title']; ?></td>
           <!--  <td><?php echo ($record['cat_parent']=="")?' --- ':$record['cat_parent']; ?></td>
           <td><?php echo $record['category_display_order']; ?></td>-->
            <td><?php echo ($record['category_status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                <?php if ($canview === true) { ?><td><?php
                    echo'<ul class = "actions">
               <li><a title = "Edit"  href = "' . Utilities::generateUrl('blogcategories', 'add', array($record['category_id'],$category_parent)) . '" ><i class ="ion-edit icon"></i></a></li>';
                    if ($record['category_status'] == 1) {
                        echo '&nbsp;<li><a title = "Manage Sub Categories"  href = "' . Utilities::generateUrl('blogcategories', 'blogchildcategories', array($record['category_id'])) . '"><i class ="ion-drag icon"></i></a></li>';
                    }
                    echo '</ul>';
                } echo "</td>";
                ?>
        </tr>
        <?php
        $i++;
    }
    ?>
</table>
<?php
$vars = array('page' => $page, 'pages' => $pages, 'start_record' => $start_record, 'end_record' => $end_record, 'total_records' => $total_records);
echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php', $vars);
?>
<script>
    $(document).ready(function () {
        //Table DND call
        $('#category').tableDnD({
            onDrop: function (tbody, row) {
                var order = $.tableDnD.serialize('id');
                order += '&catId=' + catId;
				 
                callAjax(generateUrl('blogcategories', 'setCatDisplayOrder'), order, function (t) {
                });
            }
        });
    });
</script>