<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $conf_arr_buyer_seller_advertiser_types; ?> 
<?php global $user_status;?>
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
            <li>PPC</li>
			<li>Advertisers</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Search Advertisers</h4>
							<!--<a href="" class="themebtn btn-default btn-sm">View All</a>-->				
						</div>
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $search_form->getFormHtml(); ?>		
						</div>
					</section>
					<div id="form-div"></div>
					<section class="section">
                        <div class="sectionhead"><h4>Manage - Advertisers</h4></div>						
                        <div class="sectionbody">                            
                       	<?php if (count($arr_listing)>0):?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>											  
											  <th width="15%">Name</th>
											  <th width="20%">Username/Email</th>
											  <th width="10%">Added On</th>
											  <th width="8%">Status</th>
											  <th nowrap="nowrap">Email Verified</th>
                                               <th width="6%">Balance</th>
                                              <th width="12%">Affiliate</th>
											  <th width="20%">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                         <?php foreach ($arr_listing as $sn=>$row) {  ?>
											<tr style="color:<?php if (($row['user_status']==0)){ ?>#AAAAAA<?php }?>">													
												<td><?php echo $row["user_name"]?></td>
												<td><strong>U</strong>: <?php echo $row["user_username"]?><br/><strong>E</strong>: <?php echo $row["user_email"]?></td>
												<td nowrap="nowrap"><?php echo Utilities::formatDate($row['user_added_on']); ?></td>
												<td><?php echo $user_status[$row['user_status']]; ?></td>
												<td><?php echo $row['user_email_verified']==1?"<span class='label label-success'>Verified</span>":"<span class='label label-danger'>Not Verified</span>"; ?></td>
                                                 <td><a href="<?php echo Utilities::generateUrl('users', 'balance', array($row['user_id']))?>"><?php echo Utilities::displayMoneyFormat($row['totUserBalance']); ?></a></td>
                                                <td><?php echo Utilities::displayNotApplicable($row['affiliate_name']); ?></td>
												<td nowrap="nowrap">
												<ul class="actions">
													<?php if ($row['user_status']==0):?>
														<li><a href="<?php echo Utilities::generateUrl('users', 'status', array($row['user_id'], 'unblock'))?>" title="Enable" class="toggleswitch actives" ><i class="ion-close-circled icon"></i></a></li>
												<?php  else : ?>
													  <li> <a href="<?php echo Utilities::generateUrl('users', 'status', array($row['user_id'], 'block'))?>"  title="Disable" class="toggleswitch" ><i class="ion-checkmark-circled icon"></i></a></li>
												<?php endif; ?>
													
                                                    <!--<li><a href="<?php echo Utilities::generateUrl('users', 'changepassword', array($row['user_id']))?>"  title="Change Password"><i class="ion-ios-locked icon"></i></a></li>
													<li><a href="<?php echo Utilities::generateUrl('users', 'sendemail', array($row['user_id']))?>" title="Send Email"><i class="ion-ios-email icon"></i></a></li>-->
                                                     <li><a target="_blank" href="<?php echo Utilities::generateUrl('users', 'login', array($row['user_id']))?>" title="Login into Store"><i class="ion-log-in icon"></i></a></li>
                                                     <li><a href="<?php echo Utilities::generateUrl('users', 'customer_form', array($row['user_id']))?>"  title="Edit"><i class="ion-edit icon"></i></a></li>
													<li><a onclick="return(confirm('Are you sure to delete this record?'));" href="<?php echo Utilities::generateUrl('users', 'delete', array($row['user_id']))?>" title="Delete"><i class="ion-android-delete icon"></i></a></li>
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
									'controller' => 'users',
									'action' => 'advertisers',
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