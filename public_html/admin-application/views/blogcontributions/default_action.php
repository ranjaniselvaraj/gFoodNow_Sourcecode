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
					<h1>Blog Contributions</h1>  
					<section class="section searchform_filter">
						<div class="sectionhead">
							<h4>Blog Contributions Search</h4>
							<!--<a href="" class="themebtn btn-default btn-sm">View All</a>-->				
						</div>
						<div class="sectionbody space togglewrap" style="display:none;">
							<?php echo $frmContributions->getFormHtml(); ?>
						</div>
					</section>
					<section class="section"><div id="form-div"></div>
                        <div class="sectionhead"><h4>Blog Contributions List</h4>						
						</div>
						<div class="sectionbody" id="contributions-type-list"></div>								
                        </section>
				</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				