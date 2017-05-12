<?php  defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo Settings::getSetting("CONF_WEBSITE_NAME"); ?> Reset Password</title>
<!-- Mobile Specific Metas ================================================== -->
<meta name=viewport content="width=device-width, initial-scale=1, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!-- favicon ================================================== -->
<?php if (Settings::getSetting("CONF_FAVICON")!="") {?>
<link rel="shortcut icon" href="<?php echo Utilities::generateUrl('image', 'site_favicon',array(Settings::getSetting("CONF_FAVICON")), CONF_WEBROOT_URL)?>">
<?php } ?>
<?php echo Syspage::getCssIncludeHtml(true); ?>
<?php echo Syspage::getJsIncludeHtml(false); ?>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]--> 
</head>
<body class="enterpage">   
<!--wrapper start here-->
<main id="wrapper">
        <div class="backlayer">
            <div class="layerLeft" style="background-image:url(<?php echo CONF_WEBROOT_URL?>images/admin/dealsbg.jpg); background-repeat:no-repeat;">
                <figure class="logo"><img alt="" src="<?php echo Utilities::generateUrl('image', 'site_admin_logo',array(Settings::getSetting("CONF_ADMIN_LOGO")), CONF_WEBROOT_URL)?>" title="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"></figure>
            </div>
            <div class="layerRight" style="background-image:url(<?php echo CONF_WEBROOT_URL?>images/admin/dealsbg.jpg); background-repeat:no-repeat;">
                <figure class="logo"><img alt="" src="<?php echo Utilities::generateUrl('image', 'site_admin_logo',array(Settings::getSetting("CONF_ADMIN_LOGO")), CONF_WEBROOT_URL)?>" title="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"></figure>
            </div>
        </div>		
			<div class="panels" >
            <div class="innerpanel">
				<div class="left">                    
                </div>
                <div class="right">					
                    <div class="formcontainer">
						<?php echo $frmResetPassword->getFormTag();?> 
						<?php echo $frmResetPassword->getFieldHTML('apr_id');?>						
						<?php echo $frmResetPassword->getFieldHTML('token');?>						
                            <div class="field_control fieldicon user">
                                <label class="field_label">New Password <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frmResetPassword->getFieldHTML('new_pwd');?>
                                </div>
                            </div>
                            <div class="field_control fieldicon key">
                                <label class="field_label">Confirm Password <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frmResetPassword->getFieldHTML('confirm_pwd');?>
                                </div>
                            </div>
                            <div class="field_control">                                
                                <a id="moveleft" href="<?php echo Utilities::generateUrl('admin','login_form')?>" class="linkright linkslide">Go to Login Page?</a>
                            </div>
                           <span class="circlebutton"><?php echo $frmResetPassword->getFieldHTML('btn_reset');?></span>
                           
                        </form>    
                    </div>
                </div>   
			</div>           
		</div>           
</main>
<!--wrapper end here-->
<div class="system_message" style="display:none;">
    <a class="closeMsg" href="javascript:void(0);"></a>
    <?php echo Message::getHtml();?>
</div>
<script src="<?php echo CONF_WEBROOT_URL?>public/js/admin/login_functions.js" type="text/javascript"></script>
<script>
 $(document).ready(function($) {
	$('#check-password').strength({
				strengthClass: 'strength',
				strengthMeterClass: 'strength_meter',
				strengthButtonClass: 'button_strength',
				strengthButtonText: '<?php echo Utilities::getLabel('M_Show_Password')?>',
				strengthButtonTextToggle: '<?php echo Utilities::getLabel('M_Hide_Password')?>',
				strengthVeryWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_weak')?></p>',
				strengthWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_weak')?></p>',
				strengthMediumText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_medium')?></p>',
				strengthStrongText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_strong')?></p>'
			});
	});
</script>     		
</body>
</html>
