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
			<li><a href="<?php echo Utilities::generateUrl('admin'); ?>"><img src="<?php echo CONF_WEBROOT_URL?>images/admin/home.png" alt=""> </a></li>
			<li>Import - Export Settings</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">					
					<h1>Import - Export Settings</h1>                        
					<div class="tabs_nav_container responsive flat">                            
						<ul class="tabs_nav">
							<li><a <? if ($tabSelected=="export") {?> class="active" <? }?> href="<?php echo Utilities::generateUrl('importexport','default_action',array('export')); ?>">Export</a></li>
							<li><a <? if ($tabSelected=="import") {?> class="active" <? }?> href="<?php echo Utilities::generateUrl('importexport','default_action',array('import')); ?>">Import</a></li>
							<li><a <? if ($tabSelected=="settings") {?> class="active" <? }?> href="<?php echo Utilities::generateUrl('importexport','default_action',array('settings')); ?>">Settings</a></li>													
						</ul> 
						<div class="tabs_panel_wrap">
						<div class="tabs_panel">
							<?php echo $frm->getFormHtml(); ?>
						</div>
						</div>
					</div>
			</div>
		</div>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>				