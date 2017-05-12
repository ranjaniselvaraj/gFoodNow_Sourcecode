<?php defined('SYSTEM_INIT') or die('Invalid Usage');  global $conf_option_types;?> 
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
			<li>Order Statuses</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>Manage - Order Statuses</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('orderstatus', 'form'); ?>" >Add Order Status</a></li>
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
											  <th width="90%">Name</th>
												<th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php foreach ($arr_listing as $sn=>$row) {  ?>
											<tr>
												<td><?php echo trim($row["orders_status_name"]) ?></td>
												<td class="text-center" nowrap="nowrap">
												<ul class="actions">
													<li><a href="<?php echo Utilities::generateUrl('orderstatus', 'form', array($row['orders_status_id']))?>" title="Edit"><i class="ion-edit icon"></i></a></li>
													<li><a onclick="return(confirm('Are you sure to delete this record?'));" href="<?php echo Utilities::generateUrl('orderstatus', 'delete', array($row['orders_status_id']))?>" title="Delete"><i class="ion-android-delete icon"></i></a></li>													
													</ul>
												
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
									'controller' => 'orderstatus',
									'action' => 'default_action',
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