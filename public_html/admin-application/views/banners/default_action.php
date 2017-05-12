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
            <li>CMS</li>
			<li>Banners</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">					
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>Manage - Banners</h4>
						<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                           <li><a href="<?php echo Utilities::generateUrl('banners', 'form'); ?>">Add Banner</a></li>	
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
											 <th width="30%">Title</th>
											  <th width="55%">Banner</th>
											  <th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php foreach ($arr_listing as $sn=>$row) {  ?>
											<tr class="<?php if ($row["banner_status"]==0):?>disabledRow<?php endif;?>">
												<td><?php echo $row["banner_title"];?></td>
												<td>
												<?php if ($row["banner_type"]==0):?>
													<img src="<?php echo Utilities::generateUrl('image', 'banner', array($row['banner_image_path'],'THUMB'),CONF_WEBROOT_URL)?>" alt="" />
												<?php else :
													echo $row["banner_html"];
												endif; ?></td>
												<td class="text-center" nowrap="nowrap">
												<ul class="actions">
													<?php if ($row['banner_status']==0):?>
														<li><a href="#"  title="Click to Enable" class="toggleswitch disabled" ><i onclick="UpdateBannerStatus('<?php echo $row['banner_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php  else : ?>
														  <li> <a href="#" title="Click to Disable" class="toggleswitch enabled" ><i onclick="UpdateBannerStatus('<?php echo $row['banner_id'] ?>', $(this));" class="ion-checkmark-circled icon"></i></a></li>
													<?php endif; ?>
													<li><a href="<?php echo Utilities::generateUrl('banners', 'form', array($row['banner_id']))?>" title="Edit"><i class="ion-edit icon"></i></a></li>
													<li><a  href="javascript:void(0);" onclick="ConfirmBannerDelete('<?php echo $row['banner_id'] ?>', $(this));" title="Delete"><i class="ion-android-delete icon"></i></a></li>													
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