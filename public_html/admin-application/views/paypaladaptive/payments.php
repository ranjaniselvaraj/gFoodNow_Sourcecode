<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<?php global $pp_adaptive_chained_payment_status; ?>
<div id="body">
	<!--left panel start here-->
	<?php include Utilities::getViewsPartialPath().'left.php'; ?>   
	<!--left panel end here-->
	
	<!--right panel start here-->
	<?php include Utilities::getViewsPartialPath().'right.php'; ?>   
	<!--right panel end here-->
	<!--main panel start here-->
	<div class="page">
		<ul class="breadcrumb flat">
			<li><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo CONF_WEBROOT_URL; ?>images/admin/home.png" alt=""> </a></li>
            <li>Orders</li>
			<li>PayPal Adaptive Payments</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Search PayPal Adaptive Payments</h4>
						</div>
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $search_form->getFormHtml(); ?>		
						</div>
					</section>
					<div id="form-div"></div>
					<section class="section">
                        <div class="sectionhead"><h4>Manage - PayPal Adaptive Payments</h4></div>						
                        <div class="sectionbody">                            
                       	<?php if ((count($arr_listing)>0) && (!empty($arr_listing))) :?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>											  
											  <th width="10%">Invoice</th>
											  <th width="25%">Name/Email</th>
											  <th width="10%">Added On</th>
                                              <th width="10%">Execution Date</th>
											  <th width="10%">Status</th>
                                              <!--<th width="10%">Payment Status</th>-->
											  <th width="20%">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                         <?php foreach ($arr_listing as $sn=>$row) {  ?>
											<tr>													
												<td><?php echo $row["order_invoice_number"]?></td>
												<td><strong>N</strong>: <?php echo $row["order_user_name"]?><br/><strong>E</strong>: <?php echo $row["order_user_email"]?></td>
                                               
												<td nowrap="nowrap"><?php echo Utilities::formatDate($row['order_date_added']); ?></td>
                                                <td nowrap="nowrap"><?php echo Utilities::formatDate($row['ppadappay_to_be_executed_on']); ?></td>
												<td><?php echo $pp_adaptive_chained_payment_status[$row['ppadappay_chained_payments_status']]; ?></td>
												<!--<td><?php echo $row['ppadappay_status']==-1?"Paused":"Ready for Release"; ?></td>-->
                                                 
												<td nowrap="nowrap">
													<?php if ($row["ppadappay_chained_payments_status"]==0) {?>
													<?php if ($row['ppadappay_status']==0):?>
													<a href="<?php echo Utilities::generateUrl('paypaladaptive', 'status', array($row['ppadappay_id'], 'hold'))?>">Pause</a>
													<?php  elseif ($row['ppadappay_status']==-1):  ?>
													  <a href="<?php echo Utilities::generateUrl('paypaladaptive', 'status', array($row['ppadappay_id'], 'unhold'))?>">Release</a>
													<?php endif; ?>
                                                    <?php } ?>
												
												</td>
											</tr>
											<?php }?>
											<?php else: ?>
											 <p>We are unable to find any record corresponding to your selection in this section.</p>
											<?php endif;?>                                    
                                        </tbody>    
                                    </table>                                
                                </div>	
								<div class="gap"></div>
								<div class="footinfo">
                                <aside class="grid_1">
                                    <ul class="pagination">
										<?php unset($search_parameter["url"]); ?>
                                         <?php echo Utilities::renderView(Utilities::getViewsPartialPath().'pagination.php', array(
												'start_record' => $start_record,
												'end_record' => $end_record,
												'total_records' => $total_records,
												'pages' => $pages,
												'page' => $page,
												'controller' => 'paypaladaptive',
												'action' => 'payments',
												'url_vars' => array(),
												'query_vars' => $search_parameter,
											)); ?>
                                    </ul>
                                </aside>  
								<?php  if ($total_records>0):?>
                                <aside class="grid_2"><span class="info">Showing <?php echo $start_record?> to  <?php echo $end_record?> of <?php echo $total_records?> entries</span></aside>
								<?php endif; ?>
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