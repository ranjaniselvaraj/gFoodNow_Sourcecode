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
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Search Deleted Users</h4> 			
						</div>
						
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $frmPost->getFormHtml(); ?>
						</div>
					</section>
					<section class="section"> 
                        <div class="sectionhead"><h4>Manage - Deleted Users</h4>
							<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                           <li><a href="<?php echo Utilities::generateUrl('users'); ?>">Active Users</a></li>	
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            
						</div>
						<div class="sectionbody" id="users-list"></div>								
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here--> 		