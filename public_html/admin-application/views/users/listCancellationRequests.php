<?php global $status_arr;
defined('SYSTEM_INIT') or die('Invalid Usage');
if ($records || is_array($records)) {
    echo createHiddenFormFromPost('paginateForm', '', array(), array());
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr>
        <th>ID</th>
        <th>Buyer Details</th>
        <th>Vendor Details</th>
        <th>Request Details</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Status</th>
        <?php if ($canviewcancellationrequests === true) {
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
                <td nowrap="nowrap">#C<?php echo str_pad($record["cancellation_request_id"],5,'0',STR_PAD_LEFT);?></td>
                <td nowrap="nowrap"><strong>N</strong>: <?php echo $record["user_name"]?><br/><strong>U</strong>: <?php echo $record["user_username"]?><br/><strong>E</strong>: <?php echo $record["user_email"]?><br/><strong>P</strong>: <?php echo $record["user_phone"]?></td>
                <td nowrap="nowrap"><strong>N</strong>: <?php echo $record["opr_shop_owner_name"]?><br/><strong>U</strong>: <?php echo $record["opr_shop_owner_username"]?><br/><strong>E</strong>: <?php echo $record["opr_shop_owner_email"]?><br/><strong>P</strong>: <?php echo $record["opr_shop_owner_phone"]?></td>
                <td><strong>Order:</strong> <?php echo trim($record["opr_order_invoice_number"])?><br/><strong>Status:</strong> <?php echo trim($record["orders_status_name"])?><br/><strong>Reason:</strong> <?php echo trim($record["cancelreason_title"])?><br/><strong>Comments</strong>: <span class="short"><?php echo nl2br($record["cancellation_request_message"]); ?></span></td>
                <td nowrap="nowrap"><?php echo $currencyObj->format($record["opr_net_charged"],isset($record['order_currency_code'])?$record['order_currency_code']:'',isset($record['order_currency_value'])?$record['order_currency_value']:'') ?></td>
                <td nowrap="nowrap"><?php echo Utilities::formatDate($record["cancellation_request_date"])?></td>
                <td nowrap="nowrap"><?php echo $status_arr[$record["cancellation_request_status"]]; ?></td>
                <?php if ($canviewcancellationrequests === true) { ?>
                   <td nowrap="nowrap">
                        <?php
						if ($record['cancellation_request_status']==0){
                       	 	echo '<ul class = "actions">
               					<li><a href="javascript:void(0);" title="Approve" class="toggleswitch enabled" ><i onclick="UpdateRequestStatus(' . $record['cancellation_request_id'] . ', $(this),\'approve\');" class="ion-checkmark-circled icon"></i></a></li>
								<li><a href="javascript:void(0);" title="Decline" class="toggleswitch disabled" ><i onclick="UpdateRequestStatus(' . $record['cancellation_request_id'] . ', $(this),\'decline\');" class="ion-close-circled icon"></i></a></li>
								
							</ul>';
						}
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
