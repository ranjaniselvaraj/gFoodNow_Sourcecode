<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?> 
<script type="text/javascript" src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/innovaeditor.js"></script>
<script src="<?php echo CONF_WEBROOT_URL; ?>js/LiveEditor/scripts/common/webfont.js" type="text/javascript"></script>
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
              <li>Settings</li>
              <li>General Settings</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">					
					<h1>General Settings Setup</h1>  
                      
                   <div class="tabs_nav_container responsive flat">
                            
						<ul class="tabs_nav">
							<li ><a <?php if ($tabSelected=="general") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('general')); ?>">General</a></li>
							<li ><a <?php if ($tabSelected=="local") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('local')); ?>">Local</a></li>
							<li ><a <?php if ($tabSelected=="seo") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('seo')); ?>">SEO</a></li>
							<li ><a <?php if ($tabSelected=="options") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('options')); ?>">OPTIONS</a></li>
							<!--<li ><a <?php if ($tabSelected=="cod") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('cod')); ?>">Cash on Delivery</a></li>-->
							<li ><a <?php if ($tabSelected=="live_chat") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('live_chat')); ?>">Live Chat</a></li>
							<li ><a <?php if ($tabSelected=="third_party_api") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('third_party_api')); ?>">Third Party APIs</a></li>
							<li ><a <?php if ($tabSelected=="mail") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('mail')); ?>">Email</a></li>
							<li ><a <?php if ($tabSelected=="server") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('server')); ?>">Server</a></li>
                            <li ><a <?php if ($tabSelected=="sharing") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('sharing')); ?>">Sharing</a></li>
                            <li ><a <?php if ($tabSelected=="referral") {?> class="active" <?php }?> href="<?php echo Utilities::generateUrl('configurations','default_action',array('referral')); ?>">Referral</a></li>
						</ul> 
						  <div class="tabs_panel_wrap">
							<div class="tabs_panel">
								<?php echo $frmConf->getFormHtml(); ?>
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