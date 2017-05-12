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
                	<h1>Smart Recommendations - Weightages</h1>
                    <div class="tabs_nav_container responsive flat">
						<ul class="tabs_nav">
							<li ><a class="active"  href="<?php echo Utilities::generateUrl('smartrecommendations','products'); ?>">Global</a></li>
							<li ><a href="<?php echo Utilities::generateUrl('smartrecommendations','users'); ?>">User</a></li>
						</ul> 
					</div>
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Search</h4> 			
						</div>
						
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $frmPost->getFormHtml(); ?>
						</div>
					</section>
					<section class="section"> 
                        <div class="sectionhead"><h4>Recommended Products</h4>
							<ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                           <li><a onclick="return(confirm('Are you sure to clear recommended records till date?'));" href="<?php echo Utilities::generateUrl('smartrecommendations', 'clear_records'); ?>">Clear Records</a></li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
						</div>
						<div class="sectionbody" id="recommendedproducts-list"></div>								
					</section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here--> 		