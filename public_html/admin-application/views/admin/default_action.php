<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
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
			<li>Admin Users</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
				<section class="section">
					<div class="sectionhead"><h4>Manage Admin Users</h4>
					<ul class="actions">
							<li class="droplink">
								<a href="javascript:void(0)"><i class="ion-android-more-vertical icon"></i></a>
								<div class="dropwrap">
									<ul class="linksvertical">
										<li><a href="<?php echo Utilities::generateUrl('admin', 'add'); ?>">Add New User</a></li>
									</ul>
								</div>
							</li>
						</ul>
					</div>                        
					<div class="sectionbody">                            
						<?php if (count($admins)>0):?>                           
					  <table class="table table-responsive" id="dtTable">
									<thead>
									   <tr>
										  <th width="20%">Name</th>
										  <th width="20%">Username</th>
										  <th width="20%">Email</th>
										  <th width="20%">Super Admin</th>
										  <th class="text-center">Actions</th>
									  </tr>
									</thead>  
									<tbody>
									   <?php foreach ($admins as $sn=>$row) {  ?>
										<tr>
											<td><?php echo $row["admin_full_name"]?></td>
											<td><?php echo $row["admin_username"]?></td>
											<td><?php echo $row["admin_email"]?></td>
											<td><?php echo $row['admin_is_super_admin']==1?"Y":"N"; ?></td>
											<td nowrap="nowrap" class="text-center">
												<ul class="actions">
												<?php if ($row['admin_is_super_admin']!=1) {?>	
												<li><a title="Edit" href="<?php echo Utilities::generateUrl('admin', 'edit', array($row['admin_id']))?>"><i class="ion-edit icon"></i></a></li>
                                                 <li><a  title="Change Password" href="<?php echo Utilities::generateUrl('admin', 'user_password', array($row['admin_id']))?>"><i class="ion-ios-locked icon"></i></a></li>
                                                 
												<li><a  title="permissions" href="<?php echo Utilities::generateUrl('admin', 'permissions', array($row['admin_id']))?>"><i class="ion-gear-b icon"></i></a></li>
												<li><a onclick="return(confirm('Are you sure to delete this record?'));"  title="Delete" href="<?php echo Utilities::generateUrl('admin', 'delete', array($row['admin_id']))?>"><i class="ion-android-delete icon"></i></a></li>
                                               
												<?php } ?>
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