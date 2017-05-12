<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_My_Orders')?></h3>
          
          
           <?php if ($total_records>0):?>
           <div class="darkgray-form">
            <div class="tabs-form">
              <div class="tabz-content">
              	<?php echo  str_replace("<br>", " ",$frm->getFormHtml()); ?>
              </div>
            </div>
          </div>
          <div class="tbl-listing">
            <h4><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></h4>
            <table>
              <tbody>
                <tr>
                  <th width="20%"><?php echo Utilities::getLabel('L_Inv_Number_Date')?></th>
                  <th width="40%"><?php echo Utilities::getLabel('L_Ordered_Products')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Total')?></th>
                  <th width="10%"><?php echo Utilities::getLabel('L_Status')?></th>
                  <th width="12%"><?php echo Utilities::getLabel('L_Action')?></th>
                </tr>
                <?php $cnt=0;  foreach ($arr_listing as $sn=>$row): $sn++;  ?>
                <tr>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Inv_Number_Date')?></span> <span class="blue-txt"><strong>
                  
	                  <a href="<?php echo Utilities::generateUrl('account','view_order',array($row["order_id"],$row["opr_id"]))?>" class="order">
                      <?php if (!($row["totOrders"]>1)) { echo $row["order_invoice_number"]; } else { echo $row["opr_order_invoice_number"]; } ?></a>
                  
                  </strong> </span><br>
                    <?php echo Utilities::formatDate($row["order_date_added"])?> </td>
                  <td class="cellitem"><span class="cellcaption"><?php echo Utilities::getLabel('L_Ordered_Products')?></span> <?php echo $row["opr_name"]?> (<?php echo Utilities::getLabel('L_QTY')?>:<?php echo $row["opr_qty"]?>)  </a>
                 	<?php if ($row["totOrders"]>1):?><br>
                    <?php echo Utilities::getLabel('L_Part_combined_order')?> <span class="blue-txt"> <a href="<?php echo Utilities::generateUrl('account','view_order',array($row["order_id"]))?>"><?php echo $row["order_invoice_number"]?></a> </span>
                    <?php endif;?>
                	</td>
                    <td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Total')?></span> 
					<?php if (!($row["totOrders"]>1)): 
						echo $currencyObj->format(($row["opr_net_charged"]-$row["order_discount_total"]),$row["order_currency_code"],$row["order_currency_value"]);
						else:
						echo '-';
					 endif;?></td>
                    <td nowrap="nowrap"><span class="cellcaption"><?php echo Utilities::getLabel('L_Action')?></span>
                    <?php if ($row["opr_status"]==Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS")) { ?>
                    <a href="<?php echo Utilities::generateUrl('account', 'view_return_request',array($row["refund_id"]))?>"><?php echo $row["orders_status_name"]?></a>
                    <?php } else {  echo $row["orders_status_name"];} ?>
                   </td>
                   
				   <td nowrap="nowrap">
                           
						  <a class="actions" title="<?php echo Utilities::getLabel('L_Cancel')?>" href="<?php echo Utilities::generateUrl('account', 'cancellation_request',array($row["opr_id"]))?>"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/close.svg" alt=""/></a>
                           	
                          <!--<a class="actions" title="<?php echo Utilities::getLabel('L_Send_a_message')?>" href="<?php echo Utilities::generateUrl('account', 'send_message',array("order",$row["opr_id"]))?>"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/retina/message.svg"></a>-->
                         
                          <?php if (Settings::getSetting("CONF_ALLOW_REVIEWS")):?>  		 
                          <a class="actions" title="<?php echo Utilities::getLabel('L_Feedback')?>" href="<?php echo Utilities::generateUrl('account','feedback',array($row["opr_id"]))?>"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/retina/list.svg"></a>
                          <?php endif;?>
                          
                          <a class="actions" title="<?php echo Utilities::getLabel('L_Refund')?>" href="<?php echo Utilities::generateUrl('account', 'return_request',array($row["opr_id"]))?>"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/dollar-arrow.svg" alt=""/></a>
                          
                      </td>	
                </tr>
                <?php endforeach;?>
              </tbody>
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
                    <p><?php echo Utilities::getLabel('L_You_have_not_placed_any_order')?></p>
                </div>
             </div>   
		 <?php endif;?>
          
        </div>
        
      </div>
    </div>
  </div>
