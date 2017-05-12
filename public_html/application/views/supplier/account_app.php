<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<? if ($hide_header_footer) include CONF_THEME_PATH . 'payment-header.php'; ?>
<?php if ($hide_header_footer) {
	global $tpl_for_js_css;
	$pathinfo = pathinfo($tpl_for_js_css);
	echo  '<script type="text/javascript" language="javascript" src="' . Utilities::generateUrl('pagejsandcss', 'js', array($pathinfo['dirname'],'user/account_app'), $use_root_url, false) . '&sid=' . time() . '"></script>' . "\n";
 }?>
<div> 
	<div class="body clearfix">
    	<div class="fixed-container">          
	      <div class="login-signup">    	    
        <div class="form-block fr ">
          <h2><?php echo Utilities::getLabel('M_New_User_Sign_Up_Here')?></h2>
          <div class="form-border">
	          <?php echo Utilities::displayHtmlForm($RegistrationFrm) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
 </div>