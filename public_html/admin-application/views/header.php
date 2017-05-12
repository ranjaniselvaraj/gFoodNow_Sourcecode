<?php  defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo Settings::getSetting("CONF_WEBSITE_NAME");  ?> Control Panel</title>
<!-- Mobile Specific Metas ================================================== --> 
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?php echo Syspage::getJsCssIncludeHtml(true); ?>
<?php //echo Syspage::getJsIncludeHtml(false); ?>
<?php if (Settings::getSetting("CONF_FAVICON")!="") {?>
	<link rel="shortcut icon" href="<?php echo Utilities::generateUrl('image', 'site_favicon',array(Settings::getSetting("CONF_FAVICON")), CONF_WEBROOT_URL)?>">
<?php } ?>
<?php if (!empty($dashboard_color)):?>
	<link rel="stylesheet" href="<?php echo CONF_WEBROOT_URL?>css/admin/<?php echo $dashboard_color?>.css">
<?php endif;?>
</head>
<body class="<?php echo Applicationconstants::$admin_dashboard_layout[$dashboard_layout];?>">
<!--wrapper start here-->
<main id="wrapper">   
    <!--header start here-->
	<header id="header">
        <div class="headerwrap">
            <div class="one_third_grid"><a href="javascript:void(0);" class="menutrigger"></a></div>
            <div class="one_third_grid text-center admin-logo"><a href="<?php echo Utilities::generateUrl('home'); ?>"><img src="<?php echo Utilities::generateUrl('image', 'site_admin_logo',array(Settings::getSetting("CONF_ADMIN_LOGO")), CONF_WEBROOT_URL)?>"  /></a></div>
            <div class="one_third_grid">
                <a href="<?php echo Utilities::generateUrl('admin', 'logout'); ?>" title="Logout" class="logout"></a>
                <ul class="iconmenus">                    
                    <li class="switchtoggle layoutToggle">
                        <label class="switch <?php echo ($dashboard_layout==1)?'active':''?>">
                          <span data-off="Fixed" data-on="Fluid" class="switch-label"></span>
                          <span class="switch-handle"></span>
                        </label>
                    </li>
                    <li class="droplink">
                       <a title="Your Store" href="<?php echo CONF_SERVER_PATH ?>" target="_blank"><i class="icon ion-android-globe"></i></a>
                    </li> 
                    <li class="droplink">
                       <a title="Update Sitemap" href="<?php echo Utilities::generateUrl('sitemap', 'generate'); ?>"><i class="icon ion-clipboard"></i></a>
                    </li> 
                    <li class="erase">
                       <a title="Clear Cache" href="<?php echo Utilities::generateUrl('home', 'clear'); ?>"><img src="<?php echo CONF_WEBROOT_URL;?>images/admin/erase.svg" alt="" class="iconerase"></a>
                   </li>
										
                    
                </ul>                
            </div>
        </div>  
        
        <div class="searchwrap">
            <div class="searchform"><input type="text"></div><a href="javascript:void(0)" class="searchclose searchtoggle"></a>
        </div>
        
    </header>    
    <!--header end here-->