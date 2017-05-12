<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $loggedin_user; ?>
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<!-- Basic Page Needs
  ================================================== -->
<meta charset="utf-8">
<?php Utilities::writeMetaTags();  ?>
<?php echo Settings::getSetting("CONF_SITE_TRACKER_CODE") ?>
<meta name="author" content="">
<!-- Mobile Specific Metas
  ================================================== -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<!-- CSS
  ================================================== -->
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300italic,300,400italic,600,600italic,700,700italic&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
<?php echo Syspage::getCssIncludeHtml(true); ?>
<link href='<?php echo CONF_WEBROOT_URL?>css/payment.css' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="<?php echo CONF_WEBROOT_URL?>css/theme-color.php">
<?php echo Syspage::getJsIncludeHtml(true); ?>
<script type="text/javascript">
	jQuery.Validation.setMessages(<?php echo json_encode( $validation_messages ) ?>)	
</script>
</head>
<body>
