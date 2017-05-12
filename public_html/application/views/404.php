<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<? if ($hide_header_footer) include CONF_THEME_PATH . 'payment-header.php'; ?>
<?php if ($hide_header_footer) {
	global $tpl_for_js_css;
	$pathinfo = pathinfo($tpl_for_js_css);
	echo  '<script type="text/javascript" language="javascript" src="' . Utilities::generateUrl('pagejsandcss', 'js', array($pathinfo['dirname'],'cms/view'), $use_root_url, false) . '&sid=' . time() . '"></script>' . "\n";
 }?>
<div >
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Page_not_found')?></h1>
        </div>
      </div>
      <div class="text-wrapper">
	                    <div class="title" data-content="404"> 404 </div>
                        <div class="subtitle" data-content="<?php echo Utilities::getLabel('L_We_are_sorry')?> <?php echo Utilities::getLabel('L_Page_looking_does_not_exist')?>"> <?php echo Utilities::getLabel('L_We_are_sorry')?> <?php echo Utilities::getLabel('L_Page_looking_does_not_exist')?> </div>
                        <a class="buttonNormal" href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Back_to_home')?></a>
                    </div>
      
    </div>
  </div>
  