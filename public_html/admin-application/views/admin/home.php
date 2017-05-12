<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $status_arr; global $payment_status_arr; ?> 
<div id="body">
    
    	<!--leftPanel start here-->    
        <?php include Utilities::getViewsPartialPath().'left.php'; ?>    
        <!--leftPanel end here--> 
        
        
        
        <!--rightPanel start here-->    
       <section class="rightPanel">
        	<ul class="breadcrumb">
                <li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
                <li>Dashboard</li>
            </ul>
            
            <div class="title"><h2>Dashboard </h2></div>
            			  <?php if (Admin::getAdminAccess($admin_id,DASHBOARD)):?>	
                          <section class="box">
                          	<div class="box_content">
                            	<?php echo Message::getHtml();?>
                                <div class="headingbox mediumbox">  
                               		<section class="box">
                                    	<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading" >Lifetime Sales </th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><?php echo Utilities::displayMoneyFormat($sales["lifetime_sales"])?></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                                <div class="headingbox mediumbox">  
                               		<section class="box">
                                    	<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading" >Sales Earnings</th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><?php echo Utilities::displayMoneyFormat($sales["lifetime_sales_earnings"])?></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                                
                                <div class="headingbox mediumbox">  
                               		<section class="box">
                               			<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading" >Avg Order Size</th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><?php echo Utilities::displayMoneyFormat($orders["avg_order"])?></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                                <div class="headingbox smallbox">  
                               		<section class="box">
                               			<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading" >Orders </th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><a href="<?php echo Utilities::generateUrl('orders')?>"><?php echo $orders["total_orders"]?></a></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                                <div class="headingbox smallbox">  
                               		<section class="box">
                               			<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading">Signups </th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><a href="<?php echo Utilities::generateUrl('users')?>"><?php echo $users["total_users"]?></a></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                                <div class="headingbox smallbox">  
                               		<section class="box">
                               			<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading" >Shops</th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><a href="<?php echo Utilities::generateUrl('shops')?>"><?php echo $shops["total_shops"]?></a></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                                
                                <div class="headingrightbox smallbox">  
                               		<section class="box">
                               			<table width="100%" border="0" id="dtTable">
                                     	 <tr>
                                        	<th class="highLightHeading" >Products</th>
                                      	 </tr>
                                         <tr>
                                          <td class="highLightBox"><a href="<?php echo Utilities::generateUrl('products')?>"><?php echo $products["total_products"]?></a></td>
                                        </tr>
                                    </table>
	                               </section>     
	                            </div>
                            
                            </div>
                          </section>
                          
                          <div class="box">  
                               <div class="box_head"><h3>Recent Orders</h3></div><span class="toggleall"><a href="<?php echo Utilities::generateUrl('orders')?>">View All</a></span>
                                <div class="box_content toggle_container">
                                <table class="table bordered rounded" id="dtTable">
							  	<thead>
									  <tr>
										  <th width="2%">#</th>
                                          <th width="15%">INV</th>
										  <th width="15%">Customer</th>
                                          <th width="15%">Date</th>
                                          <th width="15%">Amount</th>
                                          <th width="15%">Payment Status</th>
                                          <th width="8%"></th>
									  </tr>
							  </thead>  
							  	<tbody>
								  <?php foreach ($arr_order_listing as $sn=>$row) {  $inc++;  $order_obj = new orders($row["order_id"]); ?>
									<tr>
										<td><?php echo $inc?></td>
                                        <td><?php echo $row["order_invoice_number"]?></td>
                                        <td><?php echo $row["order_user_name"]?></td>
                                        <td><?php echo Utilities::formatDate($row["order_date_added"]) ?></td>
										<td><?php echo $currencyObj->format($row["order_net_charged"],$row['order_currency_code'],$row['order_currency_value']) ?></td>
                                         <td><?php echo $payment_status_arr[$row["order_payment_status"]]?></td>
                                        <td><a href="<?php echo Utilities::generateUrl('orders', 'view', array($row['order_id']))?>" title="View Order">View</a></td>
									</tr>
								<?php }?>
								</tbody>
							  </table>
                                </div>
                         </div>
                         
                         <?php if (count($arr_withdrawal_listing)>0):?>
                         <div class="box">  
                               <div class="box_head"><h3>Recent Withdrawal Requests</h3></div><span class="toggleall"><a href="<?php echo Utilities::generateUrl('custom','withdrawal_requests')?>">View All</a></span>
                                <div class="box_content toggle_container">
                                <table class="table bordered rounded" id="dtTable">
							  	<thead>
									  <tr>
										  <th width="8%">ID</th>
                                 		  <th width="15%">Name</th>
                                          <th width="20%">Username</th>
                                          <th width="8%">Amount</th>
                                          <th width="30%">Account Details</th>
                                          <th width="10%">Status</th>
                                          <th class="text-center">Actions</th>
									  </tr>
							  </thead>  
							  	<tbody>
								  <?php foreach ($arr_withdrawal_listing as $sn=>$row) {  $inc++; ?>
									<tr>
										<td>#<?php echo str_pad($row["withdrawal_id"],6,'0',STR_PAD_LEFT);?></td>
                                        <td><?php echo $row["user_name"]?></td>
                                        <td><strong>U</strong>: <?php echo $row["user_username"]?><br/><strong>E</strong>: <?php echo $row["user_email"]?></td>
                                        <td><?php echo Utilities::displayMoneyFormat($row["withdrawal_amount"])?></td>
                                        <td><strong>Bank Name:</strong> <?php echo trim($row["bank_name"])?><br/><strong>A/c Name</strong>: <?php echo $row['withdrawal_account_holder_name']?><br/><strong>A/c Number</strong>: <?php echo $row['withdrawal_account_number']?><br/><strong>Clabe Number</strong>: <?php echo $row['withdrawal_ssn_number']?><br/><strong>RIF Number</strong>: <?php echo $row['withdrawal_rif_number']?><br/><strong>Comments</strong>: <?php echo $row['withdrawal_comments']?></td>
                                        <td><?php echo $status_arr[$row["withdrawal_status"]]; ?></td>
                                        <td class="text-center" nowrap="nowrap">
                                            <?php if ($row["withdrawal_status"]==0):?><a href="<?php echo Utilities::generateUrl('custom', 'status', array($row['withdrawal_id'], 'approve'))?>" class="toggleswitch" title="Approve"></a>&nbsp;&nbsp;<a href="<?php echo Utilities::generateUrl('custom', 'status', array($row['withdrawal_id'], 'decline'))?>" class="toggleswitch actives" title="Decline"></a><?php endif;?>
                                        </td>
									</tr>
								<?php }?>
								</tbody>
							  </table>
                                </div>
                         </div>
                         <?php endif;?>
                          
                          <div class="box">
                         			<section class="tabcontainer">
	                       	  		 <ul class="summarydetailTabs normalTabs">
                            				<li class="active"><a href="javascript:void(0);"  rel="tabs_products">Products</a></li>
			                                <li><a href="javascript:void(0);" rel="tabs_shops">Shops</a></li>
    		                            <li><a href="javascript:void(0);" rel="tabs_signups">Signups</a></li>
            	                	</ul>
                           
                                    <div id="tabs_products" class="summarytabs_content box_content">
                   					    	<table class="table bordered rounded" id="dtTable">
										  	<thead>
												  <tr>
                                                      <th width="2%">#</th>
                                                      <th width="30%">Name</th>
                                                      <th width="15%">Brand</th>
                                                      <th width="15%">Shop</th>
                                                      <th width="5%">Available</th>
                                                      <th width="20%">Price</th>
                                                      <th width="8%"></th>
												  </tr>
										  </thead>   
										  <tbody>
										  <?php foreach ($arr_prod_listing as $sn=>$row) { $incp++;  ?>
												<tr>
													<td><?php echo $incp?></td>
                                			        <td><?php echo trim($row["prod_name"]) ?></td>
                                                    <td><?php echo $row["brand_name"] ?></td>
                                                    <td><?php echo trim($row["shop_name"]) ?></td>
                                                    <td><?php echo $row["prod_stock"] ?></td>
                                                    <td nowrap="nowrap"><?php if ($row['special']) { ?>
				                    <span style="text-decoration: line-through;">
									<?php echo Utilities::displayMoneyFormat($row['prod_sale_price']); ?></span> / 
                				    <?php echo Utilities::displayMoneyFormat($row['special']); ?>
				                    <?php } else { ?>
                					    <?php echo Utilities::displayMoneyFormat($row['prod_sale_price']); ?>
				                <?php } ?></td>
                                                    <td><a href="<?php echo Utilities::generateUrl('products', 'form', array('general',$row['prod_id']))?>" title="View">View</a></td>
									</tr>
									<?php }?>
										</tbody>
								  </table>
					               </div>
                    
                                  <div id="tabs_shops" class="summarytabs_content box_content">
                   					    
                                        <table class="table bordered rounded" id="dtTable">
                                            <thead>
                                              <tr>
                                                  <th width="3%">#</th>
                                                  <th width="12%">Shop Owner</th>
                                                  <th width="12%">Name</th>
                                                  <th width="15%">Created On</th>
                                                  <th width="15%">Active</th>
                                              </tr>
                                            </thead>   
                                            <tbody>
                                            <?php foreach ($arr_shop_listing as $sn=>$row) { $shop++; //printArray($row); ?>
                                            <tr>
                                                <td><?php echo $shop?></td>
                                                <td><?php echo $row["shop_owner_username"]?></td>
                                                <td><?php echo $row["shop_name"]?></td>
                                                <td><?php echo $row['shop_date']; ?></td>
                                                <td><?php echo $row["shop_status"]==1?"Y":"N"?></td>
                                            </tr>
                                            <?php }?>
                                            </tbody>
                                            </table>
					                  </div>
                                      <div id="tabs_signups" class="summarytabs_content box_content">
                   					    
                                        <table class="table bordered rounded" id="dtTable">
                                        <thead>
                                              <tr>
                                                  <th width="3%">#</th>
                                                  <th width="12%">Name</th>
                                                  <th width="12%">Username</th>
                                                  <th width="15%">Email</th>
                                                  <th width="15%">Phone</th>
                                                  <th width="15%">Added On</th>
                                              </tr>
                                        </thead>   
                                        <tbody>
                                        <?php foreach ($arr_member_listing as $sn=>$row) { $cust++; ?>
                                        <tr>
                                            <td><?php echo $cust?></td>
                                            <td><?php echo $row["user_name"]?></td>
                                            <td><?php echo $row["user_username"]?></td>
                                            <td><?php echo $row["user_email"]?></td>
                                            <td><?php echo $row["user_phone"]?></td>
                                            <td><?php echo $row['user_added_on']; ?></td>
                                        </tr>
                                        <?php }?>
                                        </tbody>
									  </table>
					                  </div>
                           			 </section>
                           </div>
                          
                           
                           <div class="box">  
                               <div class="box_head"><h3>Statistics</h3></div><span class="toggleall"></span>
                                <div class="box_content toggle_container">
                                <table class="table bordered rounded" id="dtTable">
							  	<thead>
									  <tr>
										    <th width="2%">#</th>
                                            <th>Today</th>
                                            <th>This Week</th>
                                            <th>This Month</th>
                                            <th>Last 3 Months</th>
                                            <th>Total</th>
									  </tr>
							  </thead>  
							  	<tbody>
									  <?php if($total_users!==false): ?>
    	                                <tr>
        	                            <th width="20%">Members Registered</th>
            	                        <td><?php echo $total_users['1']; ?></td>
                	                    <td><?php echo $total_users['7']; ?></td>
                    	                <td><?php echo $total_users['30']; ?></td>
                        	            <td><?php echo $total_users['90']; ?></td>
                            	        <td><?php echo $total_users['-1']; ?></td>
                                	   </tr>
									<?php endif; ?>
                                    <?php if($total_products!==false): ?>
    	                                <tr>
        	                            <th>Products Published</th>
            	                        <td><?php echo $total_products['1']; ?></td>
                	                    <td><?php echo $total_products['7']; ?></td>
                    	                <td><?php echo $total_products['30']; ?></td>
                        	            <td><?php echo $total_products['90']; ?></td>
                            	        <td><?php echo $total_products['-1']; ?></td>
                                	   </tr>
									<?php endif; ?>
									<?php if($total_shops!==false): ?>
    	                                <tr>
        	                            <th>Number of Shops</th>
            	                        <td><?php echo $total_shops['1']; ?></td>
                	                    <td><?php echo $total_shops['7']; ?></td>
                    	                <td><?php echo $total_shops['30']; ?></td>
                        	            <td><?php echo $total_shops['90']; ?></td>
                            	        <td><?php echo $total_shops['-1']; ?></td>
                                	   </tr>
									<?php endif; ?>
                                    <?php if($total_sales!==false): ?>
    	                               <tr>
		                    	        <th>Orders Placed Count</th>
        	                            <td><?php echo ($total_sales[0]["totalorders"])?></td>
		           	                    <td><?php echo ($total_sales[1]["totalorders"])?></td>
                	                    <td><?php echo ($total_sales[2]["totalorders"])?></td>
		           	                    <td><?php echo ($total_sales[3]["totalorders"])?></td>
                                        <td><?php echo ($total_sales[4]["totalorders"])?></td>
                                	   </tr>
                                        <tr>
		                    	        <th>Orders Placed Value</th>
        	                            <td><?php echo Utilities::displayMoneyFormat($total_sales[0]["totalsales"])?></td>
		           	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[1]["totalsales"])?></td>
                	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[2]["totalsales"])?></td>
		           	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[3]["totalsales"])?></td>
                                        <td><?php echo Utilities::displayMoneyFormat($total_sales[4]["totalsales"])?></td>
                                	   </tr>
                                       <tr>
		                    	        <th>Average Order Value</th>
        	                            <td><?php echo Utilities::displayMoneyFormat($total_sales[0]["avgorder"])?></td>
		           	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[1]["avgorder"])?></td>
                	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[2]["avgorder"])?></td>
		           	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[3]["avgorder"])?></td>
                                        <td><?php echo Utilities::displayMoneyFormat($total_sales[4]["avgorder"])?></td>
                                	   </tr>
                                       <tr>
		                    	       <th>Sales Earnings</th>
        	                            <td><?php echo Utilities::displayMoneyFormat($total_sales[0]["totalcommission"])?></td>
		           	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[1]["totalcommission"])?></td>
                	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[2]["totalcommission"])?></td>
		           	                    <td><?php echo Utilities::displayMoneyFormat($total_sales[3]["totalcommission"])?></td>
                                        <td><?php echo Utilities::displayMoneyFormat($total_sales[4]["totalcommission"])?></td>
                                	   </tr>
									<?php endif; ?>
                                    <?php if($total_withdrawal_requests!==false): ?>
    	                                <tr>
        	                            <th>Withdrawal Requests</th>
            	                        <td><?php echo $total_withdrawal_requests['1']; ?></td>
                	                    <td><?php echo $total_withdrawal_requests['7']; ?></td>
                    	                <td><?php echo $total_withdrawal_requests['30']; ?></td>
                        	            <td><?php echo $total_withdrawal_requests['90']; ?></td>
                            	        <td><?php echo $total_withdrawal_requests['-1']; ?></td>
                                	   </tr>
									<?php endif; ?>
                                    <?php if($product_reviews!==false): ?>
    	                                <tr>
        	                            <th>Product Reviews</th>
            	                        <td><?php echo $product_reviews['1']; ?></td>
                	                    <td><?php echo $product_reviews['7']; ?></td>
                    	                <td><?php echo $product_reviews['30']; ?></td>
                        	            <td><?php echo $product_reviews['90']; ?></td>
                            	        <td><?php echo $product_reviews['-1']; ?></td>
                                	   </tr>
									<?php endif; ?>
								</tbody>
							  </table>
                                </div>
                         </div>
                         
                         
                         <div class="box">
                         			<section class="tabcontainer">
	                       	  		 <ul class="detailTabs normalTabs">
                            			<li class="active"><a href="javascript:void(0);"  rel="tabs_1">Sales</a></li>
			                            <li><a href="javascript:void(0);" rel="tabs_2">Sales Earnings</a></li>
                                        <li><a href="javascript:void(0);" rel="tabs_3">Products</a></li>
    		                            <li><a href="javascript:void(0);" rel="tabs_4">Signups</a></li>
            	                	</ul>
                                     <div id="tabs_1" class="tabs_content box_content">
                   					    <img src="<?php echo Utilities::generateUrl('image', 'last_12_month_sales',array(), CONF_WEBROOT_URL."manager/")?>"  />
					                  </div>
                                     <div id="tabs_2" class="tabs_content box_content">
                   					    <img src="<?php Utilities::generateUrl('image', 'last_12_month_sales_earnings',array(), CONF_WEBROOT_URL."manager/")?>"  />
					                  </div> 
                                     <div id="tabs_3" class="tabs_content box_content">
                   					    <img src="<?php echo Utilities::generateUrl('image', 'last_12_month_products',array(), CONF_WEBROOT_URL."manager/")?>"  />
					                  </div>
                                      <div id="tabs_4" class="tabs_content box_content">
                   					    <img src="<?php echo Utilities::generateUrl('image', 'last_12_month_signups',array(), CONF_WEBROOT_URL."manager/")?>"  />
					                  </div>
                           			 </section>
                           </div>
                          <?php endif;?> 
                         <div class="clear"></div>
        </section>
        <!--rightPanel end here-->  
    </div>