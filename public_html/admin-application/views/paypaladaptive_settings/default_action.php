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
		    <li><a href="<?php echo Utilities::generateUrl('paymentmethods'); ?>">Payment Methods</a></li>
		    <li>Payment Method Setup</li>
		</ul>
		<div class="fixed_container">
			<div class="row">
				<div class="col-sm-12">
					<section class="section">
                        <div class="sectionhead"><h4>Payment Method Settings - <?php echo $payment_settings["pmethod_name"]?></h4></div>
						<p class="label label-info">Note: Please make sure cron job is set to <?php echo Utilities::generateAbsoluteUrl('paypaladaptive_pay','cron',array(),CONF_WEBROOT_URL); ?> URL with 10 minutes interval if you are going to use this payment method.</p>
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