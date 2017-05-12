<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<p class="label label-info">Please click on the caption to edit it.</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th width="6%">S. No.</th>
        <th width="24%">Key</th>
		<th width="35%">Caption (En)</th>
		<th width="35%">Caption (ALT)</th>
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
                <td><?php echo trim($record["label_key"])?></td>
                <td contenteditable="true" onBlur="saveToDatabase(this,'label_caption_en','<?php echo $record["label_id"]?>')" onClick="showEdit(this);"><?php echo html_entity_decode($record["label_caption_en"])?></td>
                <td contenteditable="true" onBlur="saveToDatabase(this,'label_caption_es','<?php echo $record["label_id"]?>')" onClick="showEdit(this);"><?php echo html_entity_decode($record["label_caption_es"])?></td>
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
<script>
var clicked = 0;
function showEdit(editableObj) {
	clicked = clicked+1;
	if (clicked==1){
		$(editableObj).html(htmlEncode(editableObj.innerHTML));
	}
	$(editableObj).css("background","#FFF");
} 

function saveToDatabase(editableObj,column,id) {
	$(editableObj).css("background","#FFF url("+webroot+"images/admin/loaderIcon.gif) no-repeat right");
	$.ajax({
		url: generateUrl("labels", "updateLabelField"),
		type: "POST",
		data:'column='+column+'&editval='+htmlDecode(editableObj.innerHTML)+'&id='+id,
		success: function(data){
			clicked = 0;
			$(editableObj).html(data);
			$(editableObj).css("background","#FDFDFD");
		}        
   });
}

</script>