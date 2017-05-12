<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <h3><?php echo Utilities::getLabel('L_My_Orders')?></h3>
          <ul class="arrowTabs">
	             <li <?php if ($sts=="all"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('affiliate','orders')?>?sts=all"><?php echo Utilities::getLabel('L_All')?></a></li>
				<li <?php if ($sts=="received"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('affiliate','orders')?>?sts=received"><?php echo Utilities::getLabel('L_Received')?></a></li>
                <li <?php if ($sts=="pending"):?> class="active" <?php endif;?>><a href="<?php echo Utilities::generateUrl('affiliate','orders')?>?sts=pending"><?php echo Utilities::getLabel('L_Pending')?></a></li>
          </ul>
          
          <?php if ($total_records>0):?>
          <div class="tbl-listing">
            <div class="darkgray-form clearfix">
            <div class="left-txt"><?php echo sprintf(Utilities::getLabel('L_Items_x_to_y_of_z_total'),$start_record,$end_record,$total_records)?></div>
          </div>
                <table>
	                <tbody>
    		            <tr>
            			    <th><?php echo Utilities::getLabel('L_Invoice_Number')?></th>
                            <th><?php echo Utilities::getLabel('L_Date')?></th>
                            <th><?php echo Utilities::getLabel('L_Order_Amount')?></th>
                            <th><?php echo Utilities::getLabel('L_Commission')?></th>
                            <th><?php echo Utilities::getLabel('L_Status')?></th>
		                </tr>
        	        <?php foreach ($affiliate_orders as $key=>$val):?>
            		    <tr>
                            <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Invoice_Number')?></span><?php echo $val["opr_order_invoice_number"]?></td>
                            <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Date')?></span><?php echo Utilities::formatDate($val["order_date_added"])?></td>
                            <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Order_Amount')?></span><?php echo Utilities::displayMoneyFormat($val["opr_net_charged"])?></td>
                            <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Commission')?></span><?php echo Utilities::displayMoneyFormat($val["opr_affiliate_commission"])?></td>
                            <td><span class="cellcaption"><?php echo Utilities::getLabel('L_Status')?></span><?php echo $val["orders_status_name"]?></td>
		                </tr>
        	        <?php endforeach;?>
                </tbody>
                </table>
            <?php if ($pages>1):?>
            <div class="pager">
            	<ul>
								 <?php unset($search_parameter["url"]); ?>
								 <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
									'start_record' => $start_record,
									'end_record' => $end_record,
									'total_records' => $total_records,
									'pages' => $pages,
									'page' => $page,
									'controller' => 'affiliate',
									'action' => 'orders',
									'url_vars' => array(),
									'query_vars' => $search_parameter,
									)); ?>
                                </ul>
              
            </div>
            <?php endif;?>
          </div>
          <?php else:?>
          <div class="space-lft-right">
                	<div class="alert alert-info">
        	        <?php echo Utilities::getLabel('L_You_do_not_have_record_this_section')?>
            		</div>
                </div>
		 <?php endif;?>
          
        </div>
        
      </div>
    </div>
  </div>
  