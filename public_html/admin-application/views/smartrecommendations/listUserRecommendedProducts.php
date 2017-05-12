<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<?php if (!empty($user)) {?>
<form class="web_form">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
          <th width="10%">S No.</th>
          <th width="40%">Product Name</th>
          <th width="30%">Shop Name</th>
          <th width="30%">Last Action</th>
          <th width="10%">Weightage</th>
    </tr>
    <?php
    if (!$records || !is_array($records)) {
        echo "<tr><td colspan=4>No Record Found</td></tr>";
    } else {
        ?>
        <?php
        $i = 0;
        foreach ($records as $record) {
            ?>
            <tr>
                    <td><?php echo ++$i;?></td>
                    <td><?php echo trim($record["prod_name"]) ?></td>
                    <td><?php echo trim($record["shop_name"]) ?></td>
                    <td><?php echo displayDate($record["last_action"],true); ?></td>
                    <td><?php echo $record["weightage"] ?></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table></form>
    <div class="gap"></div>
    	<?php 
	   }
 } else {?>
	<p>Please select a user from search box.</p>
<?php } ?>    
