<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo Settings::getSetting("CONF_WEBSITE_NAME"); ?> Forgot Password</title>
<?php echo Syspage::getCssIncludeHtml(true); ?>
<?php echo Syspage::getJsIncludeHtml(false); ?>
<?php if (Settings::getSetting("CONF_FAVICON")!="") {?>
<link rel="shortcut icon" href="<?php echo Utilities::generateUrl('image', 'site_favicon',array(Settings::getSetting("CONF_FAVICON")), CONF_WEBROOT_URL)?>">
<?php } ?>
</head>
<body>
<!--wrapper start here-->
<div id="wrapper" class="frontbg">
	<section class="frontForms">
    	<figure class="frontLogo"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>" title="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"></figure>
        <div class="whitesection">
			<?php echo Message::getHtml();?>
		    <?php echo $frmForgot->getFormHtml();?>
		   <p class="ptext">Go to Login Page? <a href="<?php echo Utilities::generateUrl('admin','login_form');?>">Click here</a></p>
        </div>
    </section>
</div>
</body>
</html>
