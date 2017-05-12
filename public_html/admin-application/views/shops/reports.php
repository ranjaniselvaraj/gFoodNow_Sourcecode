<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
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
              <li>Catalog</li>
              <li><a href="<?php echo Utilities::generateUrl('shops'); ?>">Shops</a></li>
              <li>Shops</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Manage - Shop Reports</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('shops'); ?>">Back to Shops</a></li>
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
											 <th width="5%">ID</th>
											  <th width="15%">Reported By</th>
											  <th width="20%">Report Reason</th>
											  <th width="45%">Reason Message</th>
											  <th width="15%">Date</th>
											  <th width="15%">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php $i = $start_record; foreach ($arr_listing as $sn=>$row) {  ?>
											<tr>
												<td><?php echo ++$i;?></td>
												<td><?php echo Utilities::displayNotApplicable($row["user_username"])?></td>
												<td><?php echo Utilities::displayNotApplicable($row["reportreason_title"])?></td>
												<td><span class="more"><?php echo $row["sreport_message"]?></span></td>
												<td><?php echo Utilities::formatDate($row["sreport_date"])?></td>
												<td class="center" nowrap="nowrap">
												<ul class="actions">
													<li><a href="javascript:void(0);" onclick="ConfirmReportDelete('<?php echo $row['sreport_id']?>', $(this));" title="Delete"><i class="ion-android-delete icon"></i></a></li>													
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
									'controller' => 'shops',
									'action' => 'reports',
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