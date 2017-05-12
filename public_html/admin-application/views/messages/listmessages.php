<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
          <th width="6%">S. No.</th>
          <th width="10%">From</th>
          <th width="10%">To</th>
          <th width="10%">Subject</th>
          <th width="40%">Message</th>
          <th width="13%">Date</th>
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
                <td>
                    <div class="about-me">
                       <div class="avatar"><img src="<?php echo Utilities::generateUrl('image', 'user',array($record["message_sent_by_profile"],'SMALL'),CONF_WEBROOT_URL)?>" alt=""></div>
<div class="name"><a href="<?php echo Utilities::generateUrl('users','customer_form',array($record["message_from"]));?>"><?php echo $record["message_sent_by_username"]?></a></div>
</div>
                    
                    </td>
                    <td>
                    
                    <div class="about-me">
                       <div class="avatar"><img src="<?php echo Utilities::generateUrl('image', 'user',array($record["message_sent_to_profile"],'SMALL'),CONF_WEBROOT_URL)?>" alt=""></div>
<div class="name"><a href="<?php echo Utilities::generateUrl('users','customer_form',array($record["message_to"]));?>"><?php echo $record["message_sent_to_username"]?></a></div>
</div>
                    </td>
                    <td><?php echo $record["thread_subject"]?></td>
                    <td><?php echo substringbywords($record["message_text"],250)?></td>
                    <td><?php echo Utilities::formatDate($record["message_date"],true)?></td>
	                <?php if ($canview === true) { ?>
    	            <td>
                        <?php
                        echo '<ul class = "actions">
              				 <li><a title = "View"  href = "' . Utilities::generateUrl('messages', 'view', array($record["message_thread"],$record["message_id"])) . '" ><i class ="ion-eye icon"></i></a></li>
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