<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <div class="tabz-content">
              <div class="referral">
                <div class="space-lft-right">
                  <h3><?php echo Utilities::getLabel('L_Sharing_Information')?></h3>
              	  <p><?php echo Utilities::getLabel('L_Affiliate_sharing_message')?></p><br>
                  <p><strong><?php echo Utilities::getLabel('L_You_may_copy_invitation_link_below')?></strong></p><br>
                  <div class="alert alert-refer"><i class="fa fa-exclamation-circle"></i> 
                  	<?php echo $affiliate_tracking_url?>
				  </div>
                  
                  <ul class="threecols">
                  	<?php if (!empty(Settings::getSetting("CONF_FACEBOOK_APP_ID")) && !empty(Settings::getSetting("CONF_FACEBOOK_APP_SECRET"))){?>
                    <li>
                      <div class="sharesection">
                        <div class="fbwrap"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/icon_fb.png">
                          <h3><span><?php echo Utilities::getLabel('L_Share_on')?></span> <?php echo Utilities::getLabel('L_Facebook')?></h3>
                        </div>
                        <div class="txtwrap">
                          <p><?php echo sprintf(Utilities::getLabel('L_Post_your_wall_facebook'),'<strong>'.Utilities::getLabel('L_Facebook').'</strong>')?></p>
                          <a class="btn fb " id="facebook_btn" href="javascript:void(0);"><?php echo Utilities::getLabel('L_Share')?></a> </div>
                          <span class="ajax_message" id="fb_ajax"></span>
                      </div>
                    </li>
                    <?php } ?>
                    <?php if (!empty(Settings::getSetting("CONF_TWITTER_API_KEY")) && !empty(Settings::getSetting("CONF_TWITTER_API_SECRET"))){?>
                    <li>
                      <div class="sharesection">
                        <div class="twwrap"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/icon_tw.png">
                          <h3><span><?php echo Utilities::getLabel('L_Share_on')?></span> <?php echo Utilities::getLabel('L_Twitter')?></h3>
                        </div>
                        <div class="txtwrap">
                          <p><?php echo sprintf(Utilities::getLabel('L_Send_a_tweet_followers'),'<strong>'.Utilities::getLabel('L_Tweet').'</strong>')?></p>
                          <a class="btn tw" id="twitter_btn" href="javascript:void(0);"><?php echo Utilities::getLabel('L_Share')?></a> </div>
                          <span class="ajax_message" id="twitter_ajax"></span>
                      </div>
                    </li>
                    <?php } ?>
                    <li>
                      <div class="sharesection">
                        <div class="emailwrap"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/icon_msg.png">
                          <h3><span><?php echo Utilities::getLabel('L_Share_on')?></span> <?php echo Utilities::getLabel('L_Email')?></h3>
                        </div>
                        <div class="txtwrap">
                          <p><strong><?php echo Utilities::getLabel('L_Email')?></strong> <?php echo Utilities::getLabel('L_Your_friend_tell_them_about_yourself')?></p>
                          <a class="btn email showbutton" href="javascript:void(0);"><?php echo Utilities::getLabel('L_Share')?></a> </div>
                          <span class="ajax_message"></span>
                      </div>
                    </li>
                  </ul>
           
               
             
                
                  <div style="display:none;" class="borderwrap showwrap">
                   <h3><?php echo Utilities::getLabel('L_Invite_friends_through_email')?></h3>
                    <div class="formwrap">
	                    <?php echo $sharingfrm->getFormHtml(); ?>
                        <span class="ajax_message" id="custom_ajax"></span>
                    </div>
                    
                  </div></div>
                  
                  <div class="gap"></div>
         
              </div>
            </div>
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
 $(document).ready(function() {
        $('.showbutton').click(function() {
			$(this).toggleClass("active");
                $('.showwrap').slideToggle("600");
        });
    });
	$( 'form[rel=action]' ).submit(function( event ) {
				event.preventDefault();
				var me=$(this);
				var frm=this;
				v = me.attr('validator');
				window[v].validate();
				if (!window[v].isValid()) return;
				var data = getFrmData(frm);
				callAjax($(this).attr('action'),data,function(response){
					//alert(response);
					var ans = parseJsonData(response);
					if (ans.status==true){
						$("#frmCustomShare").reset();
						$("#custom_ajax").html(ans.message);
					}
			})
		return false;					
});
	
var facebookScope = "email";
(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo Settings::getSetting("CONF_FACEBOOK_APP_ID")?>";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
								
$("#facebook_btn").click(function(event) {
		event.preventDefault();
		fbSubmit();
});
function fbSubmit() {
	FB.getLoginStatus(function(response) {
		if (response.status === 'connected') {
			facebook_redirect(response);
		} else if (response.status === 'not_authorized') {
			FB.login(function(response) {
				facebook_redirect(response);
			}, {
				scope : facebookScope
			});
		} else {
			FB.login(function(response) {
				if (response.authResponse) {
					facebook_redirect(response);
				}
			}, {
				scope : facebookScope
			});
		}
	});
}
function facebook_redirect(response_token){
	   FB.ui( {
	        method: 'feed',
        	name: "<?php echo sprintf(Settings::getSetting("CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE"),Settings::getSetting("CONF_WEBSITE_NAME"))?>",
    	    link: "<?php echo $affiliate_tracking_url?>",
			picture: "<?php echo Utilities::generateAbsoluteUrl('image', 'social_feed_image',array(Settings::getSetting("CONF_SOCIAL_FEED_IMAGE")),"/")?>",
	        caption: "<?php echo sprintf(Settings::getSetting("CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION"),Settings::getSetting("CONF_WEBSITE_NAME"))?>",
			description: "<?php echo sprintf(Settings::getSetting("CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION"),Settings::getSetting("CONF_WEBSITE_NAME"))?>",
			
    	}, 
		function( response ) {
			if ( response !== null && typeof response.post_id !== 'undefined' ) {$("#fb_ajax").html('<?php echo Utilities::getLabel('L_Thanks_for_sharing')?>');
			}
		});
}
function twitter_shared(name){
	$("#twitter_ajax").html('<?php echo Utilities::getLabel('L_Thanks_for_sharing')?>');
}
$("#twitter_btn").click(function(event) {
		event.preventDefault();
		twitter_login();
});
jQuery.fn.reset = function () {
  $(this).each (function() { this.reset(); });
}		
</script>
<?
	$_SESSION["TWITTER_URL"]=Utilities::generateAbsoluteUrl('affiliate','twitter_callback');
	$twitteroauth = new TwitterOAuth(Settings::getSetting("CONF_TWITTER_API_KEY"), Settings::getSetting("CONF_TWITTER_API_SECRET"));
	$get_twitter_url=$_SESSION["TWITTER_URL"];
	$request_token = $twitteroauth->getRequestToken($get_twitter_url);
	$_SESSION['oauth_token'] = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	if ($twitteroauth->http_code == 200) {
		$url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
		?>
        <script type="text/javascript">
		//alert('<?php echo $url?>')
        var newwindow;
        var intId;
        function twitter_login(){
            var  screenX    = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
                 screenY    = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
                 outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
                 outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
                 width    = 800,
                 height   = 600,
                 left     = parseInt(screenX + ((outerWidth - width) / 2), 10),
                 top      = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
                 features = (
                    'width=' + width +
                    ',height=' + height +
                    ',left=' + left +
                    ',top=' + top
                  );
            newwindow=window.open('<?php echo $url?>','Login_by_twitter',features);
 
           if (window.focus) {newwindow.focus()}
          return false;
        }
		</script>
        <?
	}
?>
