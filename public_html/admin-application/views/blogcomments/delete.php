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
				<section class="section">
                <div class="sectionhead"><p>You can't recover "Comment" once deleted, are you sure to delete? <a class="edit" href="<?php echo $delete_link; ?>">Yes</a> / <a class="edit" href="<?php echo Utilities::generateUrl('blogcomments'); ?>">Cancel</a></p></div>
            </section>
			</div>			
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>			