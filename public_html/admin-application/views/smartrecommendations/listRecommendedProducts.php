<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<form class="web_form">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
          <th width="30%">Name</th>
          <th width="15%">Shop</th>
          <th width="15%">System Weightage</th>
          <th width="15%">Custom Weightage</th>
          <th width="15%">Valid Till (CW)</th>
          <th width="10%">Is Excluded</th>
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
                    <td><?php echo trim($record["prod_name"]) ?></td>
                    <td><?php echo trim($record["shop_name"]) ?></td>
                    <td><?php echo $record["spw_weightage"] ?></td>
                    <td><input type="text" value="<?php echo $record["spw_custom_weightage"] ?>" onchange="javascript:saveData('<?php echo $record["spw_product_id"] ?>',this,'spw_custom_weightage');" /><span id="spw_custom_weightage-ajax-<?php echo $record["spw_product_id"]?>" class="green"></span></td>
                    <td><input type="text" value="<?php echo $record["spw_custom_weightage_valid_till"] ?>" onchange="javascript:saveData('<?php echo $record["spw_product_id"] ?>',this,'spw_custom_weightage_valid_till');" class="date" /><span id="spw_custom_weightage_valid_till-ajax-<?php echo $record["spw_product_id"]?>" class="green"></span></td>
                    <td><input id="<?php echo $record["spw_product_id"]?>" type="checkbox" class="excluded" value="1" <?php if ($record["spw_is_excluded"]==1) {?> checked="checked" <?php } ?> /><span id="spw_is_excluded-ajax-<?php echo $record["spw_product_id"]?>" class="green"></span></td>
            </tr>
            <?php
            $i++;
        }
        ?>
    </table></form>
    <div class="gap"></div>
    <?php 
    if ($pages > 1) {
        $vars = array('page' => $page, 'pages' => $pages, 'start_record' => $start_record, 'end_record' => $end_record, 'total_records' => $total_records);
        echo Utilities::renderView(Utilities::getViewsPartialPath().'backend-pagination.php', $vars);
    }
}
?>
<script>
$(function () {
	$('.date').datetimepicker({
			timepicker: false,
			format:'Y-m-d',
			formatDate:'Y-m-d',
			step: 10
	});
	
	 $('input.excluded').change(function () {
            var is_checked=0;
            var check = $(this).prop('checked');
			if (check){
				is_checked=1
			}
			var id = $(this).attr('id');
			callAjax(generateUrl("smartrecommendations", "updateProductRecommendations"), 'id='+id+'&value='+is_checked+'&field=spw_is_excluded', function(t){
			 	$('#spw_is_excluded-ajax-'+id).html('Saved').fadeIn().delay(1000).fadeOut();
			});
        });
	
	
});
</script>