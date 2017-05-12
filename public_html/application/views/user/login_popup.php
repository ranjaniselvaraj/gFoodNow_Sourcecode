<? //include CONF_THEME_PATH . 'payment-header.php'; ?>
<?php echo Message::getHtml(); ?>
	<div class="content">
    	<div class="login-signup">
    	<div class="form-block-login">
		    <div id="ajax_login_message"></div>
		    <h2><?php echo Utilities::getLabel('M_Login_Existing_User')?></h2>
		    <div class="form-border">
		    <?php echo Utilities::displayHtmlForm($frm) ?>
		    <? $fb_enabled=Settings::getSetting("CONF_ENABLE_FACEBOOK_LOGIN");
			    $gp_enabled=Settings::getSetting("CONF_ENABLE_GOOGLEPLUS_LOGIN");
		    ?>  
		    <? if ($fb_enabled || $gp_enabled):?>
		    <div class="divider"></div>
		    <h3><?php echo Utilities::getLabel('M_Or_Login_With')?></h3>
		    <div class="button-div"> <? if ($fb_enabled) :?> <a href="<?php echo Utilities::generateUrl('user', 'social_media_login',array("facebook"))?>" class="fb-color"><img src="<?php echo CONF_WEBROOT_URL?>images/fb-small.png" width="9" height="20" alt="<?php echo Utilities::getLabel('M_Facebook')?>"/> <?php echo Utilities::getLabel('M_Facebook')?></a> <? endif; ?> <? if ($gp_enabled) :?> <a href="<?php echo Utilities::generateUrl('user', 'social_media_login',array("googleplus"))?>" class="gp-color"><img src="<?php echo CONF_WEBROOT_URL?>images/g-plus.png" width="20" height="18" alt="<?php echo Utilities::getLabel('M_Google')?>"/> <?php echo Utilities::getLabel('M_Google')?></a> <? endif;?>
		    </div>
		    <? endif; ?>
	    	</div>
    	</div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $.redirect_url = $('input[type=hidden][name=redirect_url]').val();
        $('form[name="frmLogin"]').bind('submit', function(){
            $frm = $(this);
            frm_validator.validate();
            if (!frm_validator.isValid()) return;
            var data = $frm.serialize();
            data += '&btn_login=Submit&is_ajax_request=yes';
            $.ajax({
                url: $frm.attr('action'),
                type: 'post',
                data: data,
                success: function(t) {
					var ans = parseJsonData(t);
					if (ans.status==0){
						$("#ajax_login_message").html(ans.msg)
					}else{
						window.redirectUserLoggedin();
					}
                }
            });
            return false;
        });
    })
		
</script>