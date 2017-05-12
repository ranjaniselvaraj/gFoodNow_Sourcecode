<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_My_Sales')?></h3>
           <?php if ($total_records>0):?>
           <div class="darkgray-form">
            <div class="tabs-form">
              <ul class="tabz">
                <li class="active"><a href="<?php echo Utilities::generateUrl('account','sales')?>"><?php echo Utilities::getLabel('L_Sold_Items')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('account','credits')?>"><?php echo Utilities::getLabel('L_Credits_Summary')?></a></li>
              </ul>
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
                  <th><?php echo Utilities::getLabel('L_Name')?></th>
                  <th><?php echo Utilities::getLabel('L_Brand')?></th>
                  <th><?php echo Utilities::getLabel('L_Date')?></th>
                  <th><?php echo Utilities::getLabel('L_Price')?></th>
                  <th><?php echo Utilities::getLabel('L_Status')?></th>
                  <th><?php echo Utilities::getLabel('L_Action')?></th>
                </tr>
                <?php $cnt=0;  foreach ($arr_listing as $sn=>$row): $sn++;  ?>
                <tr>
                  <td width="30%"><span class="cellcaption"><?php echo Utilities::getLabel('L_Name')?></span><?php echo trim($row["opr_name"])?></td>
				  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Brand')?></span><?php echo Utilities::displayNotApplicable($row["opr_brand"]) ?></td>
 	              <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($row["order_date_added"]) ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Price')?></span><?php echo $currencyObj->format($row["opr_net_charged"],$row["order_currency_code"],$row["order_currency_value"]) ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span><?php if ($row["opr_status"]==Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS")) { ?>
                    <a href="<?php echo Utilities::generateUrl('account', 'view_return_request',array($row["refund_id"]))?>"><?php echo $row["orders_status_name"]?></a>
                    <?php } else {  echo $row["orders_status_name"];} ?></td>
                  <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Action')?></span><a class="actions" href="<?php echo Utilities::generateUrl('account', 'sales_view_order', array($row['opr_id']))?>" title="<?php echo Utilities::getLabel('L_View_Order')?>"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/view.svg" alt=""/></a> <a class="actions " href="<?php echo Utilities::generateUrl('account', 'cancel_order', array($row['opr_id']))?>" title="<?php echo Utilities::getLabel('L_Cancel_Order')?>"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/close.svg" alt=""/></a></td>
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
                        <p><?php echo Utilities::getLabel('L_You_have_not_received_any_order')?></p>
                      </div>
            </div>
		 <?php endif;?>
          
        </div>
        
      </div>
    </div>
  </div>
