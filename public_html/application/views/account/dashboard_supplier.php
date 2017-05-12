<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        
        <div class="data-side">
       	  <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <div class="tabz-content">
              <h3><?php echo Utilities::getLabel('M_Your_Product_sales')?></h3>
              <div class="orders-list">
                <ul>
                  <li> <a href="javascript:void(0);"><span class="txt-big"><?php echo Utilities::displayMoneyFormat($user_details["totalVendorSales"])?></span>
                    <p><?php echo Utilities::getLabel('M_Total_Sales')?></p>
                    </a> </li>
                  <li> <a href="<?php echo Utilities::generateUrl('account', 'credits')?>"><span class="txt-big"><?php echo Utilities::displayMoneyFormat($user_details["totUserBalance"])?> </span>
                    <p><?php echo Utilities::getLabel('M_Account_Balance')?></p></a>
                  <li><a href="<?php echo Utilities::generateUrl('account', 'sales')?>"><span class="txt-big"><?php echo $user_details["totVendorOrders"]?></span>
                    <p><?php echo Utilities::getLabel('M_Total_Orders')?></p></a>
                    </a> </li>
                  <li> <a href="javascript:void(0)"><span class="txt-big"><?php echo $user_details["totSoldQty"]?></span>
                    <p><?php echo Utilities::getLabel('M_Total_Sold_Qty')?></p>
                    </a> </li>
                </ul>
              </div>
              <div class="gap"></div>
              <div class="tbl-dashboard">
            <div class="tbl-left">
              <div class="box-head">
              </div>
              <div id="graph_parent"><div class="graph" id="monthlysalesearnings"></div></div>
            </div>
            <div class="tbl-right">
              <div class="total-stat">
                <div class="small-box">
                  <div class="caption"><?php echo Utilities::getLabel('M_Total_Products')?></div>
                  <div class="count"><?php echo $user_details["publishedItems"]?></div>
                </div>
                <div class="small-box">
                  <div class="caption"><?php echo Utilities::getLabel('M_Sold')?><br/><?php echo Utilities::getLabel('M_Quantity')?></div>
                  <div class="count"><?php echo $user_details["totSoldQty"]?></div>
                </div>
                <div class="total-earning">
                  <div class="count"><i class="svg-icn">  
<svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
<g>
	<g>
		 
		 
		<path d="M20.9,20.4c0.9,1.6,6.7,2.4,9.5,2.3c2.8-0.1,8.6-1.4,8.4-3.8
			c-0.4-4.5,2.4-10.5,2.9-11.4c1.1-1.7,1.2-5.3-3.5-3.6c-1.1,0.4-3.7,3.1-4.7-0.1c-2-5.7-6.5-4.1-7.5-0.4c-0.7,2.6-3.5,2.8-6.6,2.5
			c-3.7-0.3-4.2,2-3,3.9C17.1,10.9,19.9,18.5,20.9,20.4L20.9,20.4z M20.9,20.4"/>
	</g>
	<path fill="#ff3a59" d="M30.6,26.6c-7.5,0.3-9.7-2.5-10.1-2.3C9.4,28.1,0,39.4,0,51c0,14.8,8.4,18.7,31.9,18.7
		c23.5,0,31.9-4,31.9-18.7c0-12.4-11.4-24.2-23.2-27.6C40,23.2,38.9,26.3,30.6,26.6L30.6,26.6z M32.2,56.1v3c0,0.2-0.2,0.4-0.4,0.4
		h-2.7c-0.2,0-0.4-0.2-0.4-0.4v-2.8c-1.9-0.1-3.9-0.7-5.1-1.5c-0.2-0.1-0.2-0.3-0.2-0.5l0.9-3.1c0-0.1,0.1-0.2,0.2-0.3
		c0.1,0,0.2,0,0.4,0c0.9,0.5,2.6,1.4,4.9,1.4c1.9,0,3.1-0.8,3.1-2.1c0-1.2-0.9-2.1-3.3-2.9c-2.9-1.1-5.9-2.7-5.9-6.2
		c0-3,2-5.2,5.3-5.9v-2.8c0-0.2,0.2-0.4,0.4-0.4H32c0.2,0,0.4,0.2,0.4,0.4V35c2,0.1,3.4,0.7,4.3,1.1c0.2,0.1,0.3,0.3,0.2,0.5l-0.9,3
		c0,0.1-0.1,0.2-0.2,0.3c-0.1,0.1-0.2,0.1-0.3,0c-0.9-0.4-2.2-1.1-4.3-1.1c-1.7,0-2.7,0.7-2.7,1.9c0,1,0.9,1.8,3.7,2.8
		c3.9,1.5,5.5,3.4,5.5,6.5C37.7,53,35.6,55.4,32.2,56.1L32.2,56.1z M32.2,56.1"/>
</g>
</svg></i> <br>
                    <?php echo Utilities::displayMoneyFormat($user_details["totUserBalance"])?></div>
                  <p><strong><?php echo Utilities::getLabel('M_ACCOUNT_BALANCE')?></strong></p>
                </div>
                <a href="<?php echo Utilities::generateUrl('account', 'request_withdrawal')?>" class="btn primary-btn"><?php echo Utilities::getLabel('M_Withdraw_Money')?></a> <a href="<?php echo Utilities::generateUrl('account', 'product_form')?>" class="btn secondary-btn"><?php echo Utilities::getLabel('M_Add_Product_Shop')?></a> </div>
            </div>
          </div>
              
              
              <div class="tbl-dashboard">
            <div class="tbl-left">
              <div class="box-head">
                <h3><?php echo sprintf(Utilities::getLabel('L_Latest_x_Orders'),5)?></h3>
                <div class="btn-view"> <a href="<?php echo Utilities::generateUrl('account', 'sales')?>" class="view-more"><?php echo Utilities::getLabel('L_View_All')?> </a> </div>
              </div>
              <?php if (count($sales_orders)>0 && (!empty($sales_orders))):?>
              <div class="space-lft-right">
                <table class="cart-tbl">
                  <tbody>
                  	<?php foreach ($sales_orders as $key=>$val):?>
                    <tr>
                      <td class="relative" width="10%"><div class="pro-image"><img alt="" src="<?php echo Utilities::generateUrl('image','product_image',array($val["opr_product_id"],'THUMB'))?>"></div></td>
                      <td><div class="product-name"><a href="<?php echo Utilities::generateUrl('account','product_form',array($val["opr_product_id"]))?>"><?php echo $val["opr_name"]?></a></div>
                        <div class="product-desc">
                          <ul>
                            <li><span><?php echo Utilities::getLabel('L_Brand')?>:</span> <strong><?php echo Utilities::displayNotApplicable($val["opr_brand"])?></strong> </li>
                            <li><span><?php echo Utilities::getLabel('L_Date')?>:</span> <strong><?php echo Utilities::formatDate($val["order_date_added"]) ?></strong> </li>
                            <li><span><?php echo Utilities::getLabel('L_Status')?>:</span> <strong><?php echo $val["orders_status_name"] ?></strong></li>
                            
                          </ul>
                        </div></td>
                      <td width="18%"><div class="price"><?php echo $currencyObj->format($val["opr_net_charged"],$val["order_currency_code"],$val["order_currency_value"])?></div>
                        <a class="actions" href="<?php echo Utilities::generateUrl('account', 'sales_view_order', array($val['opr_id']))?>" title="<?php echo Utilities::getLabel('L_View_Order')?>"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/view.svg" alt=""/></a> <a class="actions" href="<?php echo Utilities::generateUrl('account', 'cancel_order', array($val['opr_id']))?>" title="<?php echo Utilities::getLabel('L_Cancel_Order')?>"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/close.svg" alt=""/></a></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php else:?>
    	      <div class="space-lft-right">
		          <div class="alert alert-info">
                        <p><?php echo Utilities::getLabel('L_You_have_not_received_any_order')?></p>
                      </div>
              </div> 
          	<?php endif;?>
              
            </div>
            <div class="tbl-right">
              <h3><?php echo Utilities::getLabel('M_Latest_Messages')?></h3>
              <div class="message-list">
                
                		<ul>
	                  	<?php if (count($messages)>0) { foreach ($messages as $key=>$val):?> 	
	                      <li>
    		                    <div class="pic">
            		            <img class="img" alt="" src="<?php echo Utilities::generateUrl('image', 'user',array($val["message_sent_by_profile"],'THUMB'))?>">
                    	    </div>
                        		<div class="text">
		                          <h4><?php echo $val["message_sent_by_username"]?></h4>
        		                  <p><?php echo substringbywords($val["message_text"],100)?></p>
                		          <a class="readmore" href="<?php echo Utilities::generateUrl('account', 'view_message',array($val["message_thread"],$val["message_id"]))?>"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/reammore.png"></a> </div>
                      	</li>
                      <?php endforeach; } else {?>
		                <li><?php echo Utilities::getLabel('M_you_do_not_have_messages')?></li>
                     <?php } ?>
                    </ul>
                
                
              </div>
            </div>
          </div>
          
          
            </div>
        </div>
      </div>
    </div>
  </div>
 <?php include CONF_THEME_PATH . $controller.'/_partial/graph.php'; ?> 