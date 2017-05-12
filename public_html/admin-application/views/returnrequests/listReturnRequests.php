<?php global $return_status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
      <th>ID</th>
      <th>User Details</th>
      <th>Product</th>
      <th>Qty</th>
      <th>Request Type</th>
      <th>Amount</th>
      <th>Date</th>
      <th>Status</th>
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
				$request_amount=($record["opr_customer_buying_price"]+$record["opr_customer_customization_price"])*$record["refund_qty"]; 
				$request_amount=$request_amount+round($request_amount*$record["order_vat_perc"]/100,2);
            ?>
            <tr>
                <td><?php echo Utilities::format_return_request_number($record["refund_id"]);?></td>
                <td><strong>N</strong>: <?php echo $record["user_name"]?><br/><strong>U</strong>: <?php echo $record["user_username"]?><br/><strong>E</strong>: <?php echo $record["user_email"]?></td>
                <td><?php echo $record["opr_name"]?></td>
                <td><?php echo $record["refund_qty"]?></td>
                <td><?php echo $record['refund_or_replace']=="RP"?Utilities::getLabel('M_Replace'):Utilities::getLabel('M_Refund')?></td>
                <td><?php echo Utilities::displayMoneyFormat($request_amount)?></td>
                <td><?php echo Utilities::formatDate($record["refund_request_date"])?></td>
                <td><?php echo $return_status_arr[$record["refund_request_status"]]; ?></td>
                <?php if ($canview === true) { ?>
                   <td nowrap="nowrap">
							<ul class="actions">
                            <li><a href="<?php echo Utilities::generateUrl('returnrequests', 'view_return_request',array($record["refund_id"]))?>" title="Edit"><i class="ion-eye icon"></i></a></li>
                            <?php if (in_array($record["refund_request_status"],array("0","1"))):?>
                            <li><a href="javascript:void(0);"  title="Approve"><i onclick="UpdateRequestStatus('<?php echo $record['refund_id'] ?>', $(this),'approve');" class="ion-checkmark-circled icon"></i></a></li>
                            <li><a href="javascript:void(0);" onclick="UpdateRequestStatus('<?php echo $record['refund_id'] ?>', $(this),'decline');" title="Cancel"><i class="ion-close-circled icon"></i></a></li>
                            <?php endif;?>												
							</ul>
                        </td>
                        <?php } ?>
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
$(document).ready(function() {
    // Configure/customize these variables.
    var showChar = 100;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show More";
    var lesstext = "Show Less";
    
    $('.short').each(function() {
        var content = $(this).html();
        if(content.length > showChar) { 
            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);
            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';
            $(this).html(html);			
        }
 
    });
	
	
	$(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
	$(".morelink").trigger( "click" );
    
});
</script>
