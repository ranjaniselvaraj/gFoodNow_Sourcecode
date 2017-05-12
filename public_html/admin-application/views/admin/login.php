<?php  defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo Settings::getSetting("CONF_WEBSITE_NAME"); ?> Login</title>
<!-- Mobile Specific Metas ================================================== -->
<meta name=viewport content="width=device-width, initial-scale=1, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<!-- favicon ================================================== -->
<?php if (Settings::getSetting("CONF_FAVICON")!="") {?>
<link rel="shortcut icon" href="<?php echo Utilities::generateUrl('image', 'site_favicon',array(Settings::getSetting("CONF_FAVICON")), CONF_WEBROOT_URL)?>">
<?php } ?>
<?php echo Syspage::getCssIncludeHtml(true); ?>
<?php echo Syspage::getJsIncludeHtml(false); ?>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]--> 
</head>
<body class="enterpage" >   
<!--wrapper start here-->
<main id="wrapper">
        <div class="backlayer">
            <div class="layerLeft" style="background-image:url(<?php echo CONF_WEBROOT_URL?>images/admin/dealsbg.jpg); background-repeat:no-repeat;">
                <figure class="logo"><img alt="" src="<?php echo Utilities::generateUrl('image', 'site_admin_logo',array(Settings::getSetting("CONF_ADMIN_LOGO")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>" title="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"></figure>
            </div>
            <div class="layerRight" style="background-image:url(<?php echo CONF_WEBROOT_URL?>images/admin/dealsbg.jpg); background-repeat:no-repeat;">
                <figure class="logo"><img alt="" src="<?php echo Utilities::generateUrl('image', 'site_admin_logo',array(Settings::getSetting("CONF_ADMIN_LOGO")), CONF_WEBROOT_URL)?>" title="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"></figure>
            </div>
        </div>		
			<div class="panels" >
            <div class="innerpanel">
				<div class="left">
                    <div class="formcontainer">						
                        <h5>Forgot your password? </h5>
                        <h6>Enter The E-mail Address Associated With Your Account.</h6>
                         <?php echo $frmForgot->getFormTag();?>						
                            <div class="field_control fieldicon mail">
                                <label class="field_label">Email <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frmForgot->getFieldHTML('admin_email');?>
                                </div>
                            </div>
                            <?php if (!empty(CONF_RECAPTACHA_SITEKEY)){ ?>
                            <div class="field_control fieldicon">
                                <div class="field_cover">
                                   <?php echo $frmForgot->getFieldHTML('captcha_code');?>                                </div>
                            </div>
                            <? } ?>
                            
                            <!--<button class="circlebutton"></button>-->
                            <span class="circlebutton"> <?php echo $frmForgot->getFieldHTML('btn_forgot');?></span>
                            <!--<a href="javascript:void(0)" class="circlebutton">Sign In</a>-->
                            <a id="moveright" href="javascript:void(0);" class="linkright linkslide">Back to Login</a>
                        </form>    
                    </div>
                </div>
                <div class="right">					
                    <div class="formcontainer">
						<?php echo $frmLogin->getFormTag();?>                        
                            <div class="field_control fieldicon user">
                                <label class="field_label">Username <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frmLogin->getFieldHTML('username');?>
                                </div>
                            </div>
                            <div class="field_control fieldicon key">
                                <label class="field_label">Password <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php echo $frmLogin->getFieldHTML('password');?>
                                </div>
                            </div>
                            
                            <!--<div class="field_control fieldicon ">
                                <label class="field_label">Remember Me <span class="mandatory">*</span></label>
                                <div class="field_cover">
                                    <?php //echo $frmLogin->getFieldHTML('remember');?>
                                </div>
                            </div>-->
							<div class="field_control">
                                <label class="checkbox leftlabel"><?php echo $frmLogin->getFieldHTML('remember');?><i class="input-helper"></i>Remember me</label>
                                <a id="moveleft" href="javascript:void(0);" class="linkright linkslide">Forgot Password?</a>
                            </div>	
                            <!--<div class="field_control">                                
                                <a id="moveright" href="<?php echo CONF_SERVER_PATH?>" target="_blank" class="linkleft">Your Store</a> <a id="moveleft" href="javascript:void(0);" class="linkright linkslide">Forgot Password?</a>
                            </div>-->
                           <span class="circlebutton"><?php echo $frmLogin->getFieldHTML('btn_submit');?></span>
                           
                        </form>    
                    </div>
                </div>   
			</div>           
		</div>           
</main>
<!--wrapper end here-->
<div class="system_message" style="display:none;">
    <a class="closeMsg" href="javascript:void(0);"></a>
    <?php echo Message::getHtml();?>
</div>
<script src="<?php echo CONF_WEBROOT_URL?>public/js/admin/login_functions.js" type="text/javascript"></script>
<script language="javascript">
<?php if($_SERVER['HTTP_REFERER']){?>
$('#username').on("input",function() {
	$('input[type="password"]').closest('.field_control').addClass('active');
});
<?php }?>
$('input[type="password"], #username').on("input",function() {
	$(this).focus();
});
function chkLoginForgotForm(str){
	if(str=='FORGOT'){ 
		if($( document ).width()>990){
			$('.panels').css('margin-left','0px');
			setTimeout(function(){	  
				$('.innerpanel').css('margin-left','100%');
			}, 100);	
		}else{
			$('.enterpage').addClass('active-left');
		}				
	}
}
chkLoginForgotForm('<?php echo $frmType;?>');
</script>
<script src='https://www.google.com/recaptcha/api.js'></script>
</body>
</html>
