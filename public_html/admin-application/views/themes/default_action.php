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
		<?php echo html_entity_decode($breadcrumb); ?>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>Manage - Themes</h4>
						</div>
                        <div class="sectionbody">                            
                         <?php if ((count($arr_listing)>0) && (!empty($arr_listing))) :?>
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>
											   <th width="40%">Name</th>
                                               <th width="20%">Primary Color</th>
                                               <th width="20%">Color</th>
											   <th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php foreach ($arr_listing as $sn=>$row) {  ?>
											<tr> 
												<td><?php echo trim($row["theme_name"]) ?><?php if (Settings::getSetting("CONF_FRONT_THEME")==$row['theme_id']) :?>
                                          	 	&nbsp;<i class="ion-checkmark-circled icon"></i>
                                                <?php endif;?></td>
                                                <td>#<?php echo ($row["theme_primary_color"]) ?></td>
                                                <td><ul class="colorpallets"><li><a style="width:100px;background-color:#<?php echo $row["theme_primary_color"]?>" href="javascript:void(0)" class="color_red"></a></li></ul></td>
												<td class="left" nowrap="nowrap">
												<ul class="actions">
													
                                                    <li><a  href="javascript:void(0);" onclick="ActivateTheme('<?php echo $row['theme_id'] ?>', $(this));" title="Activate"><i class="ion-checkmark-circled icon"></i></a></li>	
                                                    <li><a href="<?php echo Utilities::generateUrl('themes', 'preview', array($row['theme_id']))?>" title="Preview" target="_blank"><i class="ion-arrow-expand icon"></i></a></li>
                                                     <li><a href="<?php echo Utilities::generateUrl('themes', 'theme_clone', array($row['theme_id']))?>" title="Clone"><i class="ion-plus icon"></i></a></li>
                                                    <?php if ($row["theme_added_by"]>0):?>
                                                    <li><a href="<?php echo Utilities::generateUrl('themes', 'form', array($row['theme_id']))?>" title="Edit"><i class="ion-edit icon"></i></a></li>
													<li><a  href="javascript:void(0);" onclick="ConfirmDelete('<?php echo $row['theme_id'] ?>', $(this));" title="Delete"><i class="ion-android-delete icon"></i></a></li>													
                                                    <? endif; ?>
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