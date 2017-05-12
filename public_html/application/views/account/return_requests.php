<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $return_status_arr;  ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('M_Return_Requests')?></h3>
          <ul class="arrowTabs">
	            <li <?php if ($sts=="all"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('account','return_requests')?>?sts=all"><?php echo Utilities::getLabel('L_All')?></a></li>
                <?php if ($is_buyer_logged):?> 
				<li <?php if ($sts=="sent"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('account','return_requests')?>?sts=sent"><?php echo Utilities::getLabel('L_Sent')?></a></li>
                <?php endif; ?>
                <?php if ($is_seller_logged):?>
                <li <?php if ($sts=="received"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('account','return_requests')?>?sts=received"><?php echo Utilities::getLabel('L_Received')?></a></li>
                 <?php endif; ?>
          </ul>
          
          <?php if (count($arr_listing)>0):?>
          <div class="tbl-listing">
            <table>
              <tr>
                <th width="3%">ID</th>
                <th width="15%"><?php echo Utilities::getLabel('L_Date')?></th>
                <th width="22%"><?php echo Utilities::getLabel('L_Inv_Number')?></th>
                <th width="35%"><?php echo Utilities::getLabel('L_Products')?></th>
                <th width="15%"><?php echo Utilities::getLabel('L_Return_Qty')?></th>
                <th width="15%"><?php echo Utilities::getLabel('L_Status')?></th>
                <th width="6%"><?php echo Utilities::getLabel('L_Action')?></th>
              </tr>
              <?php $cnt=0; foreach ($arr_listing as $sn=>$row): $sn++;  ?>
              <tr>
                                      <td><span class="cellcaption">ID</span><?php echo Utilities::format_return_request_number($row["refund_id"]);?></td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($row["refund_request_date"])?></td>
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Order_Inv_Number')?></span>
                                      <span class="cellcaption"><?php echo Utilities::getLabel('L_Order_Inv_Number')?></span>
                                      	 <?php echo $row["opr_order_invoice_number"]?>
                                      </td>
                                      <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Ordered_Products')?></span><?php echo $row["opr_name"]?></td>
                                      <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Return_Qty')?></span><?php echo $row["refund_qty"]?></td>
                                      
                                      <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span>
                                      	<?php echo $return_status_arr[$row["refund_request_status"]]; ?>
                                      </td>
                                      
                                      <td>
                                         <a class="actions" title="<?php echo Utilities::getLabel('L_View_Request')?>" href="<?php echo Utilities::generateUrl('account','view_return_request',array($row["refund_id"]))?>"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/action-feedback_icon.png"></a>
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
    	    			<?php echo Utilities::getLabel('L_You_do_not_have_return_request')?>
		        	</div>
              </div> 
          <?php endif;?>
        </div>
        
      </div>
    </div>
  </div>
  