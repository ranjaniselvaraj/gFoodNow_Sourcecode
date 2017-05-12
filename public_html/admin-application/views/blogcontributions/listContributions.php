<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
 if ($records || is_array($records)) {
 echo createHiddenFormFromPost('paginateForm', '', array(), array());
 }
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th >S. No.</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Status</th>
        <?php if ($canview === true) {
            echo '<th>Action</th>';
        } ?>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo '<tr><td colspan=5>No Record Found!!</td></tr>';
    } else {
       
        ?>
    <?php
    $i = $start_record;
    foreach ($records as $record) {
        ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo ucfirst($record['contribution_author_first_name']); ?></td>
                <td><?php echo ucfirst($record['contribution_author_last_name']); ?></td>
                <td><?php echo $record['contribution_author_email']; ?></td>
                <td>
                    <?php
                    if ($record['contribution_status'] == 1) {
                        echo 'Approved';
                    } elseif ($record['contribution_status'] == 2) {
                        echo 'Posted';
                    } elseif ($record['contribution_status'] == 3) {
                        echo 'Rejected';
                    } else {
                        echo 'Pending';
                    }
                    ?></td>
                <td>
                 <ul class = "actions">
            <?php if ($canview === true) { ?><li><a title = "View" href="<?php echo Utilities::generateUrl('blogcontributions', 'view', array($record['contribution_id'])); ?>" ><i class ="ion-ios-eye icon"></i></a></li> <?php } ?>
            <?php if ($canview === true) { ?><li><a title = "Delete" onclick="return confirmDelete(this);" data-href="<?php echo Utilities::generateUrl('blogcontributions', 'delete', array($record['contribution_id'], $record['contribution_file_name'])); ?>" ><i class ="ion-android-delete icon"></i></a></li><?php } ?></ul></td>
                </td>
            </tr>
        <?php
        $i++;
    }
    ?>
    </table>
     <?php 
                 $vars = array('page'=>$page,'pages'=>$pages,'start_record'=>$start_record,'end_record'=>$end_record,'total_records'=>$total_records);
                 echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php',$vars); ?>
    <?php }
?>