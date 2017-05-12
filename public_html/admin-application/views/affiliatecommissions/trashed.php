<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ;?> 
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
			<li>Affiliate Commission Settings</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Trashed - Affiliate Commission Settings</h4>
	                        <ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                            <li><a href="<?php echo Utilities::generateUrl('affiliatecommissions'); ?>">Active Settings</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            
                        </div>
						
                        <div class="sectionbody">                            
							<div class="box_content clearfix toggle_container">
                            	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal">
									<tr>
									<td colspan="2">
										<table id="commission_form_table" class="table_listing table">
											<thead>
												<tr>
													<th width="35%">Category</th>
													<th width="35%">Affiliate</th>
													<th width="20%">Fees [%]</th>
												</tr>
											</thead>
											<tbody>
											<?php $comm_row = 0; ?>
											<?php foreach ($commission_settings as $commsetting) { ?>
											<tr id="comm-row<?php echo $commsetting['afcommsetting_id']; ?>">
												<td><?php echo Utilities::displayNotApplicable($commsetting["category_name"])?></td>
												<td><?php echo Utilities::displayNotApplicable($commsetting["affiliate"])?></td>
        	                                    <td><?php echo $commsetting["afcommsetting_fees"]?></td>
											</tr>
											<?php $comm_row++; ?>
											
											
											<?php } ?>
											</tbody>
										</table>
									</td>
									</tr>
									
									</table>
									
	                        </div>                     
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
