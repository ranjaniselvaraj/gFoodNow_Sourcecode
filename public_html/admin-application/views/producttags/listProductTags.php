<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
$vk=0;
$tag_string = '';
foreach ($records as $record) {
	$vk++;
	if ($vk<4){
		$tag_string.=isset($record['url_alias_keyword'])?$record['url_alias_keyword']:''."+";
	}
}
$tag_string=trim($tag_string,'+');
if (empty($tag_string))
$tag_string = 'best-discount+shirts+christmas-offer';
?>
<p class="label label-info">Note: To attach one or more tags please use tags keyword separated by "+" in URL. Example <?php echo Utilities::generateAbsoluteUrl('tags','view',array(),CONF_WEBROOT_URL)?>?tags=<?php echo $tag_string;?></p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="20%">Name</th>
        <th width="20%">Keyword</th>
        <th width="35%">Front URL</th>
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
                <td><?php echo trim($record["ptag_name"]) ?></td>
                <td><?php echo trim($record["seo_url_keyword"]) ?></td>
                <td><a href="<?php echo Utilities::generateAbsoluteUrl('tags','view',array(),CONF_WEBROOT_URL) ?>?tags=<?=$record['seo_url_keyword']?>" target="_blank"><?php echo Utilities::generateAbsoluteUrl('tags','view',array(),CONF_WEBROOT_URL) ?>?tags=<?=$record['seo_url_keyword']?></a></td>
                
                <?php if ($canview === true) { ?>
                    
                    <td>
                        <?php
                        echo '<ul class = "actions">
               <li><a title = "Edit"  href = "' . Utilities::generateUrl('producttags', 'form', array($record['ptag_id'])) . '" ><i class ="ion-edit icon"></i></a></li>
               <li><a title = "Delete" href="javascript:void(0);" onclick="ConfirmProductTagDelete(' . $record['ptag_id'] . ', $(this));" ><i class ="ion-android-delete icon"></i></a></li>
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