<?php  defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo Settings::getSetting("CONF_WEBSITE_NAME");  ?> Control Panel</title>
<!-- Mobile Specific Metas ================================================== --> 
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php echo Syspage::getCssIncludeHtml(true); ?>
<?php echo Syspage::getJsIncludeHtml(false); ?>
</head>
<body class="<?php echo Applicationconstants::$admin_dashboard_layout[$dashboard_layout];?>">
<!--wrapper start here-->
<main id="wrapper">   
    <!--header start here-->
	<header id="header">
        <div class="headerwrap">
            <div class="one_third_grid"></div>
            <div class="one_third_grid">
            	<div class="text-center"><a href="<?php echo Utilities::generateUrl('themes','activate',array($theme)); ?>" class="clear_btn orange">Activate Theme</a></div>
            </div>
            <div class="one_third_grid"></div>
        </div>  
        
        
        
    </header>    
    <!--header end here-->
    
    
<div id="body">
	
	<!--main panel start here-->
	<div>
		<iframe id="theme_preview_iframe" src="<?php echo Utilities::getBaseUrl(); ?>" data="<?php echo $theme?>"></iframe>
	</div>          
	<!--main panel end here-->
</div>
<!--body end here-->
</div>
<style>
#theme_preview_iframe {
    width: 100%;
	height:1200px;
    margin: 0 auto;
}
</style>				