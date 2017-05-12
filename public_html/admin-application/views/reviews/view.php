<?php defined('SYSTEM_INIT') or die('Invalid Usage');  global $review_status;?> 
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
					<section class="section">
					<div class="sectionhead"><h4>Product Review </h4> 
	                        <ul class="actions">
                                <li class="droplink">
                                    <a href="javascript:void(0);"><i class="ion-android-more-vertical icon"></i></a>
                                    <div class="dropwrap">
                                        <ul class="linksvertical">
                                        	<li><a href="<?php echo Utilities::generateUrl('reviews'); ?>">Back to Reviews</a></li>
                                            
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            
                        </div>
					<div class="sectionbody">
						<?php echo $frm->getFormHtml(); ?>
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
