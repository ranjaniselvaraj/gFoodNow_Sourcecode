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
			<li><a href="<?php echo Utilities::generateUrl('admin'); ?>">Admin Users</a></li>
			<li>Admin Users Setup</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Admin User Setup</h4></div>
                        <div class="sectionbody">
							<?php echo $frmEdit->getFormHtml(); ?>
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