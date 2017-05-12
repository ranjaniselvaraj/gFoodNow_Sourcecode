<?php defined('SYSTEM_INIT') or die('Invalid Usage');?> 
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
                        <div class="sectionhead"><h4>Manage - Email Templates</h4></div>						
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
											<tr style="color:<?php if (($row['tpl_status']==0)){ ?>#AAAAAA<?php }?>">
												<td><?php echo trim($row["tpl_name"]) ?></td>
												<td class="text-center" nowrap="nowrap">
												<ul class="actions">
													<?php if ($row['tpl_status']==0):?>
														<li><a href="#"  title="Click to Enable" class="toggleswitch disabled" ><i onclick="UpdateStatus('<?php echo $row['tpl_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php  else : ?>
														  <li> <a href="#"   title="Click to Disable" class="toggleswitch enabled" ><i onclick="UpdateStatus('<?php echo $row['tpl_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php endif; ?>
													<li><a href="<?php echo Utilities::generateUrl('emailtemplates', 'form', array($row['tpl_id']))?>" title="Edit"><i class="ion-edit icon"></i></a></li>																							
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
																
                        </section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				