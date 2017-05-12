<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    	<div class="fixed-container">
          <span class="gap"><br/></span>
	      <div class="login-signup">
    	    <div class="form-block fl ">
        	  <h2><?php echo Utilities::getLabel('M_Login_Existing_User')?></h2>
	          <div class="form-border">
				<?php //echo Utilities::displayHtmlForm($loginFrm) ?>
                <?php echo $loginFrm->getFormHtml();?>  
                <? $fb_enabled=Settings::getSetting("CONF_ENABLE_FACEBOOK_LOGIN");
				   $gp_enabled=Settings::getSetting("CONF_ENABLE_GOOGLEPLUS_LOGIN");
				?>  
				<? if ($fb_enabled || $gp_enabled):?>
        	    <div class="divider"></div>
            	<h3><?php echo Utilities::getLabel('M_Or_Login_With')?></h3>
	            <div class="button-div"> <? if ($fb_enabled) :?> <a href="javascript:void(0);" onclick="fbLogin();" class="fb-color"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/fb-login.svg" alt="<?php echo Utilities::getLabel('M_Facebook')?>"/> <?php echo Utilities::getLabel('M_Facebook')?></a> <? endif; ?> <? if ($gp_enabled) :?> <a href="<?php echo Utilities::generateUrl('user', 'social_media_login',array("googleplus"))?>" class="gp-color"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/gp-login.svg" alt="<?php echo Utilities::getLabel('M_Google')?>"/> <?php echo Utilities::getLabel('M_Google')?></a> <? endif;?>
              </div>
              <? endif; ?>
          </div>
        </div>
        <div class="form-block fr ">
          <h2><?php echo Utilities::getLabel('M_New_User_Sign_Up_Here')?></h2>
          <div class="form-border">
	          <?php echo $RegistrationFrm->getFormHtml();?>
          </div>
        </div>
      </div>
    </div>
  </div>
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
 <script type="text/javascript">
 window.fbAsyncInit = function() {
    // FB JavaScript SDK configuration and setup
    FB.init({
      appId      : '<?php echo Settings::getSetting("CONF_FACEBOOK_APP_ID")?>', // FB App ID
      cookie     : true,  // enable cookies to allow the server to access the session
      xfbml      : true,  // parse social plugins on this page
      version    : 'v2.8' // use graph api version 2.8
    });
    
    // Check whether the user already logged in
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            //display user data
            //getFbUserData();
        }
    });
};

// Load the JavaScript SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// Facebook login with JavaScript SDK
function fbLogin() {
    FB.login(function (response) {
        if (response.authResponse) {
            getFbUserData();
        } else {
            document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
        }
    }, {scope: 'email'});
}

// Fetch the user profile data from facebook
function getFbUserData(){
    FB.api('/me', {locale: 'en_US', fields: 'id,first_name,last_name,email,picture'},
    function (response) {
		 saveUserData(response);
    });
}

function saveUserData(userData){
	$.mbsmessage('<?php echo Utilities::getLabel('M_Please_wait')?>...');
    $.post(generateUrl('user', 'fblogin'), {oauth_provider:'facebook',userData: JSON.stringify(userData)}, function(data){
				$(document).trigger('close.mbsmessage');   
				var json = parseJsonData(data);
				if(json.status=="1"){
					window.location.reload();
				}else{
					ShowJsSystemMessage(json.msg,true,true);
				}
		
		return true; 
	});
}

  </script>