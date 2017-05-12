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
            <li>Settings</li>
			<li>Database Backup & Restore</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Database Backup & Restore</h4>
						</div>
						<div class="sectionbody space togglewrap">
							<?php echo $backup_frm->getFormHtml(); ?>	
						</div>
					</section>
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Database Upload</h4>
						</div>
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $upload_frm->getFormHtml(); ?>	
						</div>
					</section>
					<section class="section"> <div id="form-div"></div>
                        <div class="sectionhead"><h4>Manage - Database Backup</h4>						
						</div>
						
                        <div class="sectionbody">                            
                         <?php if (count($files_array)>0):?>    
                          <table class="table table-responsive" id="dtTable">
                                        <thead>
                                           <tr>
											 <th width="50%">Backup File Name</th>
											  <th width="30%"></th>
											  <th class="text-center">Actions</th>
										  </tr>
                                        </thead>  
                                        <tbody>
                                          <?php 
										  foreach ($files_array as $sn=>$row) {  ?>
											<tr>
												<td><?php echo $row?></td>	
												<td><?php echo date("d/m/Y H:i:s", filectime(CONF_DB_BACKUP_DIRECTORY_FULL_PATH."/".$row));?></td>
												<td class="text-center" nowrap="nowrap">
												<ul class="actions">
													<li><a href="javascript:void(0);" onclick="window.open('<?php echo Utilities::generateUrl('databasebackuprestore', 'download', array($row))?>');" title="Download Database"><i class="ion-code-download icon"></i></a></li>
													<li><a onclick="return(confirm('Are you sure to restore database to this record?'));" href="<?php echo Utilities::generateUrl('databasebackuprestore', 'restore', array($row))?>" title="Restore Database"><i class="ion-reply icon"></i></a></li>
													<li><a  onclick="return(confirm('Are you sure to delete this record?'));" href="<?php echo Utilities::generateUrl('databasebackuprestore', 'delete', array($row))?>" title="Delete"><i class="ion-android-delete icon"></i></a></li>													
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