<?php defined('SYSTEM_INIT') or die('Invalid Usage');  global $status_arr;?> 
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<?php echo html_entity_decode($breadcrumb); ?>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">					
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>Report - Sales for <?php echo Utilities::formatDate($date)?></h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('reports','sales'); ?>">Back to Reports</a></li>
                                            <li><a href="<?php echo $_SERVER['REQUEST_URI']?>&export"  target="_blank">Export</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						
                        <div class="sectionbody">                            
                         <?php if ((count($arr_listing)>0) && (!empty($arr_listing))) :?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>
											  <th>#</th>
											  <th>Invoice Number</th>
											  <th>Customer</th>
											  <th>No. of Qty.</th>
											  <th>Sub Total</th>
											  <th>Tax</th>
											  <th>Shipping</th>
											  <th>Total</th>
											  <th>Refunded Qty.</th>
											  <th>Refunded Amount</th>
											  <th>Refunded Tax</th>
											  <th>Sales Earnings</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                         <?php foreach ($arr_listing as $sn=>$row) { $sn++ ?>
										<tr>
											<td><?php echo $sn;?></td>
											<td><?php echo $row["opr_order_invoice_number"]?></td>
											<td><?php echo $row["order_user_name"]?></td>
											<td><?php echo $row["opr_qty"]?></td>
											<td><?php echo $currencyObj->format($row["cart_total"],$row["order_currency_code"],$row["order_currency_value"])?></td>
											<td><?php echo $currencyObj->format($row["tax_charged"],$row["order_currency_code"],$row["order_currency_value"])?></td>
											<td><?php echo $currencyObj->format($row["opr_shipping_charges"],$row["order_currency_code"],$row["order_currency_value"])?></td>
											<td><?php echo $currencyObj->format($row["opr_net_charged"],$row["order_currency_code"],$row["order_currency_value"])?></td>
											<td><?php echo $row["opr_refund_qty"]?></td>
											<td><?php echo $currencyObj->format($row["opr_refund_amount"],$row["order_currency_code"],$row["order_currency_value"])?></td>
											<td><?php echo $currencyObj->format($row["opr_refund_tax"],$row["order_currency_code"],$row["order_currency_value"])?></td>
											<td><?php echo $currencyObj->format($row["net_sales_earnings"],$row["order_currency_code"],$row["order_currency_value"])?></td>
										</tr>
										<?php }?>
										<?php else: ?>
										 <p>We are unable to find any record corresponding to your selection in this section.</p>
										<?php endif;?>                                    
                                        </tbody>    
                                    </table>                                
                                </div>																	
                        </section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				