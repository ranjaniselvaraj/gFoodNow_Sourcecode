
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
<div class="fixed-demo-btn no-print" id="demo-btn" ><a id="btn-demo" href="http://www.fatbit.com/website-design-company/multi-vendor-ecommerce-system.html?q=yodemo" target="_blank" class="">More About Yokart</a></div>
<div class="theme-switcher">
 <ul class="colorpallets fronttheme front-color-theme-switcher">
 	<?php foreach ($front_themes as $theme) {?>
 		<li class="theme-0 <?php if($front_theme==$theme['theme_id'] ){echo "active";}?>" data-theme='<?php echo $theme['theme_id']?>'  ><a href="javascript:void(0)" style="background-color:#<?php echo $theme['theme_primary_color']?>" ></a></li>
    <?php }?>
    </ul>
</div>
<script type="text/javascript">
		$(document).ready(function() { 
			var w = $(window).width();
			if(w < 1023) {
				$("#demo-btn").hide();
		}
	});
</script>
<? } ?>
<?php echo Settings::getSetting("CONF_SITE_TRACKER_CODE") ?>
  
<div class="system_message" style="display:none;">
    <a class="closeMsg" href="javascript:void(0);"></a>
    <?php echo Message::getHtml();?>
</div>
</body>
</html>  