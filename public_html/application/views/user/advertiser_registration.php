<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php include CONF_THEME_PATH . 'short_header.php'; ?>
<div class="wrapper">
    <div class="flat-header clearfix">
        <div class="">
          <?php
                $mobile_logo_icon=Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON")!=""?Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON"):Settings::getSetting("CONF_FRONT_LOGO");
              ?>
              <div id="logo"><a href="<?php echo Utilities::getSiteUrl(); ?>"><span class="small-logo"><img src="<?php echo Utilities::generateUrl('image', 'mobile_icon_logo',array($mobile_logo_icon), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> <span class="medium-logo"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> </a> </div>
              
          <div class="right-seller-info">
            <div class="seller-login-trigger"> <a class="seller_login_toggle" href="javascript:void(0)"></a> </div>
            <div class="seller-frm">
            	<?php echo $quickLoginFrm->getFormHtml(); ?>
            </div>
          </div>
        </div>
      </div>
	
    <div id="body" class="body">
    <div id="main-area">
	    <div class="body clearfix">
	      <div class="fixed-container">
    	    <div class="cmsContainer">
        	 <h3><?php echo Utilities::getLabel('L_Advertise_With_Us')?></h3>
	          <p><?php echo Utilities::getLabel('L_To_Create_an_advertiser_account')?></p>
        	      <?php echo $RegistrationFrm->getFormHtml(); ?>
	        </div>
    	  </div>
	    </div>
	</div>
   </div>
  </div>  
<div class="system_message" style="display:none;">
    <a class="closeMsg" href="javascript:void(0);"></a>
    <?php echo Message::getHtml();?>
</div>
</body>
</html>
<script type="text/javascript"> $('.seller_login_toggle').click(function() { $('html').toggleClass("seller_login-active"); }); </script>