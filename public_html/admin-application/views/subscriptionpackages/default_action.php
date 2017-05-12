<?php defined('SYSTEM_INIT') or die('Invalid Usage');  ?> 
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
                        <div class="sectionhead"><h4>Manage - Subscription Packages</h4>
						
						</div>
						
                        <div class="sectionbody">                            
                         <?php if ((count($arr_listing)>0) && (!empty($arr_listing))) :?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>											 
											  <th width="20%">Name</th>
											  <th class="center" width="30%">No. of Allowed Products</th>
											  <th class="center" width="30%">Images per product</th>
											  <th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php foreach ($arr_listing as $sn=>$row) {  ?>
											<tr class="<?php if ($row["merchantpack_active"]==0):?>disabledRow<?php endif;?>">
												<td><?=$row["merchantpack_name"] ?></td>
											
												<td class="center"><?=$row["merchantpack_max_products"] ?></td>
												<td class="center"><?=$row["merchantpack_images_per_product"] ?></td>
												<td class="text-center" nowrap="nowrap">
												<ul class="actions">
													<?php if ($row['merchantpack_active']==0):?>
														<li><a href="#"  title="Click to Enable" class="toggleswitch disabled" ><i onclick="UpdateStatus('<?php echo $row['merchantpack_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php  else : ?>
														  <li> <a href="#"   title="Click to Disable" class="toggleswitch enabled" ><i onclick="UpdateStatus('<?php echo $row['merchantpack_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php endif; ?>
													<li><a href="<?php echo Utilities::generateUrl('SubscriptionPackages', 'form', array($row['merchantpack_id']))?>" title="Edit"><i class="ion-edit icon"></i></a></li>
																									
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
																
                        </section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				