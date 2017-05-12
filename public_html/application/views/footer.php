<?php if (!$short_header_footer) {?>
<footer class="footer clearfix">
      <div class="desktop-footer">
        <div class="ft-top clearfix">
          <div class="fixed-container">
            <div class="ft-section clearfix">
              <div class="f-cell first">
                <div class="f-logo">
                	
                    <?php if (Settings::getSetting("CONF_FOOTER_LOGO_GRAPHIC")!="") {?>
                	<img src="<?php echo Utilities::generateUrl('image', 'footer_logo_graphic',array(Settings::getSetting("CONF_FOOTER_LOGO_GRAPHIC")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/>
                    <?php } ?>
                  <p class="copyright "><?php echo sprintf(Utilities::getLabel('M_copyright_text'),date("Y"),Settings::getSetting("CONF_WEBSITE_NAME"))?>
                  </p>
                  <?php if(Utilities::isBrandingSignatureAuthenticated()){ ?>
                  	<p class="copyright ">Powered by <a href="http://www.yo-kart.com">Yo!Kart</a></p>
				  <?php }?>
                </div>
              </div>
              <?php foreach($footer_navigation as $fkey=>$fval):?>
              <div class="f-cell cl-effect-21 ">
                <h3><?php echo $fval[0]['parent']; ?></h3>
                <ul>
                   <?php foreach($fval as $link): ?>
            		<?php if($link['navlink_type']==0): ?>
                    <li><a target="<?php echo $link['navlink_href_target']?>" href="<?php echo Utilities::generateUrl('cms', 'view', array($link['navlink_cmspage_id'])); ?>"><?php echo $link['navlink_caption']; ?></a></li>
                    <?php elseif($link['navlink_type']==1): ?>
                    <li><?php echo $link['navlink_caption']; ?></li>
                    <?php elseif($link['navlink_type']==2): $url=str_replace('{SITEROOT}', CONF_WEBROOT_URL, $link['navlink_html']); ?>
                    <li><a target="<?php echo $link['navlink_href_target']?>" href="<?php echo $url?>"><?php echo $link['navlink_caption']; ?></a></li>
                    <?php endif; ?>
					<?php endforeach;?>
                </ul>
              </div>
              <?php endforeach;?>
              
              <div class="f-cell social">
                <h3><?php echo Utilities::getLabel('M_Keep_in_touch')?></h3>
                <ul>
                <?php foreach ($social_platforms as $socialplatform):?>
				    <li><a href="<?php echo $socialplatform['splatform_url']?>" target="_blank"><img src="<?php echo Utilities::generateUrl('image', 'social_platform_icon', array($socialplatform["splatform_icon_file"]),CONF_WEBROOT_URL)?>" alt="<?php echo Utilities::getLabel('L_Facebook')?>"/></a></li>
                 <?php endforeach;?>   
              </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="ft-bttm clearfix">
          <div class="fixed-container">
            
            <div class="ft-bttm-left">
              <?php if (Settings::getSetting("CONF_ENABLE_NEWSLETTER_SUBSCRIPTION")) :?>	
	             <?php if (Settings::getSetting("CONF_NEWSLETTER_SYSTEM")=="Mailchimp") :?>
             	 <div class="sign-up-form">
                	<h4><?php echo Utilities::getLabel('M_Subscribe_to_newsletter')?></h4>
	                <p><?php echo Utilities::getLabel('M_Subscribe_newsletter_text')?></p>
        	      	 <?php echo $footerNewsletterForm->getFormHtml();?>
            	        <span id="ajax_newsletter_message"></span>
                	    <div class="clear"></div>
               	 		<p></p>
	              </div>
	              <?php elseif (Settings::getSetting("CONF_NEWSLETTER_SYSTEM")=="Aweber") :?>
    	            <?php echo Settings::getSetting("CONF_AWEBER_SIGNUP_CODE");?>   
        	      <?php endif; ?>
              <?php endif; ?>
            </div>
            
            
            <div class="ft-bttm-right">
              <?php echo Utilities::renderHtml($footer_content_block)?> 	
              <div class="clear"></div>
              <?php if ((count($top_brands)>0) && !empty($top_brands)):?>
              <div class="tags-links">
                <h3><?php echo Utilities::getLabel('M_Top_Brands')?></h3>
                <div class="links">
				<?php foreach($top_brands as $key=>$val):?>
			    	<a href="<?php echo Utilities::generateUrl('brands','view',array($val["brand_id"]))?>"><?php echo $val["brand_name"]?></a>
                <?php endforeach;?>
              	<a href="<?php echo Utilities::generateUrl('brands','all')?>"><?php echo Utilities::getLabel('M_View_all')?></a></div>
              </div>
              <?php endif; ?>
              <div class="ft-section payment-types clearfix">
                <div class="cell fl"> <img src="<?php echo CONF_WEBROOT_URL?>images/payment-cards.png"  alt="<?php echo Utilities::getLabel('L_Payment_cards')?>"/> </div>
                <div class="cell fr"> <img src="<?php echo CONF_WEBROOT_URL?>images/secure.png"   alt="<?php echo Utilities::getLabel('L_Secure_Payments')?>"/></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  <?php } else {?>
  <footer id="smallfooter" class="footer clearfix no-print">
    <div class="fixed-container">
      <div class="desktop-footer">
        <div class="ft-bttm-right">
             <?php echo Utilities::renderHtml($footer_content_block)?> 	
        </div>
      </div>
    </div>
  </footer>
  <?php }?>
  <p id="back-top">
		<a title="Back to Top" href="#top">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 200.387 200.387" style="enable-background:new 0 0 200.387 200.387;" xml:space="preserve" width="256px" height="256px">
				<g>
					<g>
						<g>
							<polygon points="5.504,154.449 0,149.102 100.197,45.938 200.387,149.102 194.893,154.449 100.197,56.947         " fill="#FFFFFF" />
						</g>
					</g>
					<polygon points="100.197,45.938 0,149.102 5.504,154.449 100.197,56.947 194.893,154.449 200.387,149.102     " fill="#FFFFFF" />
				</g>
			</svg>
		</a>
	</p>
</div>
<?php 
$livechat_enabled=Settings::getSetting("CONF_ENABLE_LIVECHAT");
if ($livechat_enabled) { ?>
<div class="chat no-print"><?php echo Settings::getSetting("CONF_LIVE_CHAT_CODE")?></div>
<?php } ?>
<?php
		$m_time = explode(" ",microtime());
		$m_time = $m_time[0] + $m_time[1];
		$loadend = $m_time;
		$loadtotal = ($loadend - $loadstart);
		//echo "<small class='no-print'><em>". round($loadtotal,3) ." seconds</em></small>";
?>
<script src="<?php echo CONF_WEBROOT_URL?>js/common-function.js" type="text/javascript"></script>
<? if (((strpos($_SERVER['HTTP_HOST'],"yo-kart.com")!== false)  || (strpos($_SERVER['HTTP_HOST'],"localhost")!== false)) && (strpos($_SERVER['HTTP_HOST'], 'mcnation') === false)){?>
<div class="fixed-demo-btn no-print" id="demo-btn" ><a id="btn-demo" href="javascript:void(0);" class="request-demo">Request a Demo</a></div>
<div class="theme-switcher">
 <ul class="colorpallets fronttheme front-color-theme-switcher">
 	<?php foreach ($front_themes as $theme) {?>
 		<li class="theme-0 <?php if($front_theme==$theme['theme_id'] ){echo "active";}?>" data-theme='<?php echo $theme['theme_id']?>'  ><a href="javascript:void(0)" style="background-color:#<?php echo $theme['theme_primary_color']?>" ></a></li>
    <?php }?>
    </ul>
</div>
<script type="text/javascript">
		/*$(document).ready(function() { 
			var w = $(window).width();
			if(w < 1023) {
				$("#demo-btn").hide();
		}
	});*/
</script>
<? } ?>
<?php echo Settings::getSetting("CONF_SITE_TRACKER_CODE") ?>
  
<div class="system_message" style="display:none;">
    <a class="closeMsg" href="javascript:void(0);"></a>
    <?php echo Message::getHtml();?>
</div>
<script>
window.loclSettings = {
    app_id: 'web',
    account_id: 'AQCxdlUn9KV',
    bot_id: 'Bofg1BVa5HU',
    base_url: 'https://s3.amazonaws.com/widget.locl.co'
};
</script>
<script>!function(){function t(){var t=n.createElement("script");t.type="text/javascript",t.async=!0,t.src=e.loclSettings.base_url+"/js/embed.js?x="+Math.random();var a=n.getElementsByTagName("script")[0];a.parentNode.insertBefore(t,a)}var e=window,a=e.Locl;if("function"==typeof a)a("reattach_activator"),a("update",loclSettings);else{var n=document,c=function(){c.c(arguments)};c.q=[],c.c=function(t){c.q.push(t)},e.Locl=c,e.attachEvent?e.attachEvent("onload",t):e.addEventListener("load",t,!1)}}();</script>
</body>
</html>  