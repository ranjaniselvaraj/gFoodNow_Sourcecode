<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr; ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('M_Cancellation_Requests')?></h3>
          <ul class="arrowTabs">
	             <li <?php if ($sts=="all"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('account','cancellation_requests')?>?sts=all"><?php echo Utilities::getLabel('L_All')?></a></li>
				<li <?php if ($sts=="sent"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('account','cancellation_requests')?>?sts=sent"><?php echo Utilities::getLabel('L_Sent')?></a></li>
                <li <?php if ($sts=="received"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('account','cancellation_requests')?>?sts=received"><?php echo Utilities::getLabel('L_Received')?></a></li>
          </ul>
          <?php if (count($arr_listing)>0 && (!empty($arr_listing))):?>
          <div class="tbl-listing">
            <table>
              <tr>
                <th width="5%">ID</th>
                <th width="15%"><?php echo Utilities::getLabel('L_Date')?></th>
                <th width="20%"><?php echo Utilities::getLabel('L_Inv_Number')?></th>
                <th width="45%"><?php echo Utilities::getLabel('L_Request_Details')?></th>
                <th width="15%"><?php echo Utilities::getLabel('L_Status')?></th>
              </tr>
              <?php $cnt=0; foreach ($arr_listing as $sn=>$row): $sn++;  ?>
              <tr>
                                      <td><span class="cellcaption">ID</span>C<?php echo str_pad($row["cancellation_request_id"],5,'0',STR_PAD_LEFT);?></td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($row["cancellation_request_date"])?></td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Order_Inv_Number')?></span>
                                      	 <?php echo $row["opr_order_invoice_number"]?>
                                      </td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Request_Details')?></span>
                                      	<strong><?php echo Utilities::getLabel('L_Reason')?></strong>: <?php echo $row["cancelreason_title"]; ?><br/>
                                        <strong><?php echo Utilities::getLabel('L_Comments')?></strong>: <span class="short"><?php echo nl2br($row["cancellation_request_message"]); ?></span>
                                      </td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span>
                                      	<?php echo $status_arr[$row["cancellation_request_status"]]; ?>
                                      </td>
                                      
              </tr>
              <?php endforeach;?>
            </table>
            <?php if ($pages>1):?>
            <div class="pager">
              <ul>
              <?php echo getPageString('<li><a href="javascript:void(0)" onclick="listPages(xxpagexx);">xxpagexx</a></li>', $pages, $page,'<li class="active"><a  href="javascript:void(0)">xxpagexx</a></li>', '<li>...</li>');?>
              </ul>
            </div>
            <?php endif;?>
          </div>
          <?php else:?>
    	      <div class="space-lft-right">
		          <div class="alert alert-info">
        		        <?php echo Utilities::getLabel('L_You_not_have_any_cancellation_request')?>
		          </div>
              </div> 
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
  $(document).ready(function() {
    // Configure/customize these variables.
    var showChar = 100;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "<?php echo Utilities::getLabel('M_Show_more')?>";
    var lesstext = "<?php echo Utilities::getLabel('M_Show_less')?>";
    
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
    
});
</script>  
  