<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $loggedin_user; ?>
<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en" prefix="og: http://ogp.me/ns#">
<!--<![endif]-->
<head>
<!-- Basic Page Needs
  ================================================== -->
<meta charset="utf-8">
<?php if (isset($blogMetaData) and $blogMetaData['meta_title'] != "") {		 
		Utilities::writeBlogMetaTags($blogMetaData);
	} else {
		Utilities::writeMetaTags(); 
	}  ?>
<meta name="author" content="">
<!-- Mobile Specific Metas
  ================================================== -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<? if (strpos($_SERVER['HTTP_HOST'],"yo-kart.com")!== false){?>
<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
<? } ?>
<!-- CSS
  ================================================== -->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300italic,300,400italic,600,600italic,700,700italic&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
<?php echo Syspage::getJsCssIncludeHtml(true); ?>
<link rel="stylesheet" href="<?php echo CONF_WEBROOT_URL?>css/theme-color.php">
<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!-- Adding "maximum-scale=1" fixes the Mobile Safari auto-zoom bug: http://filamentgroup.com/examples/iosScaleBug/ -->
<!-- All JavaScript at the bottom, except for Modernizr and Respond.
Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries -->
<script type="text/javascript">
	jQuery.Validation.setMessages(<?php echo json_encode( $validation_messages ) ?>)	
</script>
<!-- Favicons ================================================== -->
<?php if (Settings::getSetting("CONF_FAVICON")!="") {?>
<link rel="shortcut icon" href="<?php echo Utilities::generateUrl('image', 'site_favicon',array(Settings::getSetting("CONF_FAVICON")), CONF_WEBROOT_URL)?>">
<?php }?>
<?php if (Settings::getSetting("CONF_APPLE_TOUCH_ICON")!="") {?>
<link rel="apple-touch-icon" href="<?php echo Utilities::generateUrl('image', 'apple_touch_icon',array(Settings::getSetting("CONF_APPLE_TOUCH_ICON")), CONF_WEBROOT_URL)?>">
<?php } ?>
<link href='<?php echo $_COOKIE['_theme']?>' rel='stylesheet' type='text/css' id="theme-switcher-css" />
<meta name=viewport content="width=device-width, initial-scale=1, user-scalable=no">
<?php if(isset($socialShareContent) && !empty($socialShareContent)){?>
	<!-- OG Product Facebook Meta [ -->
	<meta property="og:type" content="<?php echo $socialShareContent['type']; ?>" />
	<meta property="og:title" content="<?php echo $socialShareContent['title']; ?>" />
	<meta property="og:site_name" content="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>" />
	<meta property="og:image" content="<?php echo $socialShareContent['image']; ?>" />
	<meta property="og:url" content="<?php echo Utilities::getCurrUrl(); ?>" />
	<meta property="og:description" content="<?php echo $socialShareContent['description']; ?>" />
	<!-- ]   -->
	<!--Here is the Twitter Card code for this product  --> 
	<?php if (!empty(Settings::getSetting("CONF_TWITTER_USERNAME"))){?>
	<meta name="twitter:card" content="<?php echo $socialShareContent['type']; ?>">
	<meta name="twitter:site" content="@<?php echo Settings::getSetting("CONF_TWITTER_USERNAME")?>">
	<meta name="twitter:title" content="<?php echo $socialShareContent['title']; ?>">
	<meta name="twitter:description" content="<?php echo $socialShareContent['description']; ?>">
	<meta name="twitter:image:src" content="<?php echo $socialShareContent['image']; ?>">
		
	<?php } ?>
	<!-- End Here is the Twitter Card code for this product  --> 
<?php }?>
</head>
<body <?php if (($controller=="products") && ($action=="view")) {?> class="product-detail-page" <?php }?>  >
<div class="wrapper"  >
  <?php if (!$short_header_footer) {?>	
  <div class="navigations__overlay"></div>
  <header class="header">
    <div id="top" class="clearfix">
      <div class="fixed-container">
        <div class="pull-left">
          <p class="note"><?php echo Utilities::getLabel('L_FREE_SHIPPING_RETURNS_ALL_ORDERS')?></p>
        </div>
        <div class="pull-right">
       	  <?php if($is_front_user_logged == true){ ?>
          <nav class="shortnav">
                <ul>
                    <li><?php echo Utilities::getLabel('F_Welcome')?> <span><?php echo $logged_user_name;?></span></li>
                </ul>
           </nav>
           <?php } ?> 
          <ul class="fav">
          	<?php if($is_front_user_logged == false){ 
				$become_seller_page=str_replace('{SITEROOT}', CONF_WEBROOT_URL, Settings::getSetting("CONF_SELL_SITENAME_PAGE"));
			?>
            	<li class="sell_with_sitename"> <a href="<?php echo $become_seller_page?>"><i>  
					<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
viewBox="0 0 366.79 366.79" style="enable-background:new 0 0 366.79 366.79;" xml:space="preserve">
<g>
<g id="Layer_5_38_">
<path d="M331.82,63.705c1.444,3.582-0.534,6.512-4.395,6.512H39.364c-3.861,0-5.92-2.961-4.575-6.58L55.981,6.581
C57.326,2.962,61.585,0,65.445,0h233.676c3.861,0,8.201,2.929,9.646,6.51L331.82,63.705z"/>
<path d="M32.344,106.039c0,14.053,11.392,25.444,25.445,25.444c10.579,0,19.647-6.455,23.484-15.642
c0.761-1.821,2.52-1.795,3.288,0.038c3.846,9.167,12.906,15.604,23.469,15.604c10.609,0,19.701-6.494,23.52-15.724
c0.742-1.796,2.419-1.94,3.12-0.203c3.768,9.337,12.915,15.927,23.603,15.927c10.687,0,19.832-6.588,23.6-15.926
c0.701-1.737,2.341-1.737,3.042,0c3.769,9.338,12.915,15.926,23.601,15.926c10.687,0,19.833-6.588,23.6-15.926
c0.703-1.737,2.34-1.737,3.039,0c3.769,9.337,12.916,15.927,23.604,15.927c10.688,0,19.834-6.588,23.602-15.926
c0.699-1.737,2.339-1.737,3.038,0c3.769,9.337,12.914,15.927,23.603,15.927c14.053,0,25.445-11.392,25.445-25.444V95.285
c0,0,0-5.021-5.302-5.021c-72.441,0-215.995,0-289.76,0c-7.04,0-7.04,5.219-7.04,5.219L32.344,106.039z"/>
<path d="M327.065,342.699c0-0.005,0.001-0.009,0.001-0.013V241.792c0-6.12-5.009-11.128-11.127-11.128H50.851
c-6.12,0-11.128,5.008-11.128,11.128v100.894c0,0.658,0,3.59,0,4.445c0,10.858,8.802,19.659,19.659,19.659
c7.443,0,13.904-4.144,17.237-10.247c0.476-0.873,0.818-2.729,5.548-2.729h202.764c4.451,0,5.272,1.833,5.74,2.693
c3.327,6.122,9.799,10.283,17.256,10.283c10.857,0,19.14-8.801,19.14-19.659C327.067,345.605,327.065,345.217,327.065,342.699z"/>
<path d="M91.226,148.782c-0.133-1.813-1.642-3.258-3.485-3.258H77.602c-1.931,0-3.51,1.579-3.51,3.51v0.046v58.106v0.046
c0,1.932,1.58,3.51,3.51,3.51h10.139c1.844,0,3.353-1.445,3.485-3.258c0.009-0.03,0.025-0.06,0.025-0.091v-0.161v-58.199v-0.161
C91.251,148.841,91.235,148.812,91.226,148.782z"/>
</g>
</g> 
</svg>
</i><?php echo sprintf(Utilities::getLabel('M_Sell_with_sitename'),Settings::getSetting("CONF_WEBSITE_NAME"))?> </a> </li>
            <?php } ?>
            
            <li> <a href="<?php echo Utilities::generateUrl('cart')?>" ><i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
              <path fill="#ff3a59" d="M70.1,11.7h-7l-7,21H25.7l-7-23.3c0,0-2.8-9.2-7-9.3h-7c0,0-5.5,1.9-4.7,4.7c0,0,0.3,6.7,9.3,2.3l11.7,35
	h39.6L70.1,11.7z"/>
              <path fill="#ff3a59" d="M29.2,46.3c3.2,0,5.8,2.6,5.8,5.8s-2.6,5.8-5.8,5.8s-5.8-2.6-5.8-5.8C23.4,49,26,46.3,29.2,46.3z"/>
              <path fill="#ff3a59" d="M52.4,46.3c3.2,0,5.8,2.6,5.8,5.8s-2.6,5.8-5.8,5.8s-5.8-2.6-5.8-5.8C46.5,49,49.2,46.3,52.4,46.3z"/>
              <rect x="24.4" y="10.5" fill="#ff3a59" width="28" height="7"/>
              <rect x="34.9" y="0" fill="#ff3a59" width="7" height="28"/>
              </svg> </i><?php echo Utilities::getLabel('M_Shipping_Cart')?></a> </li>
            <li> <a href="<?php echo Utilities::generateUrl('cart','checkout')?>"><i> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="15 -14 70 70" enable-background="new 15 -14 70 70" xml:space="preserve">
              <path fill="#ff3a59" d="M57.8,1.6C30.6,5.4,18.9,24.9,15,44.3c9.7-13.6,23.4-19.8,42.8-19.8v15.9L85,13.2L57.8-14V1.6L57.8,1.6z"/>
              </svg> </i> <?php echo Utilities::getLabel('M_Checkout')?> </a> </li>
          </ul>
          
        </div>
      </div>
    </div>
    <div class="mainhead">
      <div class="top-head">
        <div class="fixed-container">
          <a href="javascript:void(0)" class="navs_toggle"><span></span></a>
          <?php
		  	$mobile_logo_icon=Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON")!=""?Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON"):Settings::getSetting("CONF_FRONT_LOGO");
          ?>
          <div id="logo"><a href="<?php echo Utilities::getSiteUrl(); ?>"><span class="small-logo"><img src="<?php echo Utilities::generateUrl('image', 'mobile_icon_logo',array($mobile_logo_icon), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> <span class="medium-logo"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> </a> </div>
          
          
          <div class="mobile-element">
            <div class="app-style">
              <div class="search-triger"><a href="#" class="click_trigger" id="ct_5"><span class="icon-search"><i class="svg-icn">
                <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill="#ff3a59" d="M68,58.5L47.5,38c5.5-9.7,4.1-22.3-4.1-30.5c-9.9-9.9-26-9.9-35.9,0c-9.9,9.9-9.9,26,0,35.9
	c8.3,8.3,20.8,9.6,30.5,4.1L58.5,68c2.6,2.6,6.9,2.6,9.5,0C70.6,65.3,70.6,61.1,68,58.5z M38.3,38.3c-7.1,7.1-18.7,7.1-25.8,0
	c-7.1-7.1-7.1-18.7,0-25.8c7.1-7.1,18.7-7.1,25.8,0C45.4,19.6,45.4,31.2,38.3,38.3z"/>
                </svg>
                </i></span></a></div>
              <div class="cart"><a href="<?php echo Utilities::generateUrl('cart')?>"> <i class="svg-icn">
                <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                  <path fill-rule="evenodd" clip-rule="evenodd" fill="#ff3a59" d="M70.1,11.7h-7l-7,21H25.7l-7-23.3c0,0-2.8-9.2-7-9.3h-7
	c0,0-5.5,1.9-4.7,4.7c0,0,0.3,6.7,9.3,2.3l11.7,35h39.6L70.1,11.7z"/>
                  <path fill-rule="evenodd" clip-rule="evenodd" fill="#ff3a59" d="M29.2,46.3c3.2,0,5.8,2.6,5.8,5.8c0,3.2-2.6,5.8-5.8,5.8
	s-5.8-2.6-5.8-5.8C23.4,49,26,46.3,29.2,46.3z"/>
                  <path fill-rule="evenodd" clip-rule="evenodd" fill="#ff3a59" d="M52.4,46.3c3.2,0,5.8,2.6,5.8,5.8c0,3.2-2.6,5.8-5.8,5.8
	s-5.8-2.6-5.8-5.8C46.5,49,49.2,46.3,52.4,46.3z"/>
                </svg>
                </i> <span class="count_cart_items">(<?php echo $cart_items?>)</span></a></div>
              <div class="myaccount"> <a href="#" class="clickme click_trigger" id="ct_1"> <span></span></a>
                <div class="listing-account-nav" id="list_ct_1">
                  <ul >
                  	<?php foreach($top_header_mobile_navigation as $link):?>
                    <?php if($link['nl_type']==0): ?>
                    <li><a target="<?php echo $link['nl_target']?>" href="<?php echo Utilities::generateUrl('cms', 'view', array($link['nl_cms_page_id'])); ?>"><?php echo $link['nl_caption']; ?></a></li>
                    <?php elseif($link['nl_type']==1): ?>
                    <li><?php echo $link['nl_caption']; ?></li>
                    <?php elseif($link['nl_type']==2): $url=str_replace('{SITEROOT}', CONF_WEBROOT_URL, $link['nl_html']); ?>
                    <li><a target="<?php echo $link['nl_target']?>" href="<?php echo $url?>"><?php echo $link['nl_caption']; ?></a></li>
                    <?php endif; ?>
                    <?php endforeach;?>
		           </ul>
                </div>
              </div>
            </div>
          </div>
          
          <div class="right-side" id="list_ct_5">
            <div id="search">
	            <?php echo $headerSearchForm->getFormHtml();?>
	            <?php if($is_front_user_logged == true){  $dashboard_url=Utilities::generateUrl('account'); ?>
              		<a class="btn primary-btn" href="<?php echo $dashboard_url?>"><?php echo Utilities::getLabel('M_DASHBOARD')?></a> <span class="or"><?php echo Utilities::getLabel('M_or')?></span> <a class="btn secondary-btn" href="<?php echo Utilities::generateUrl('account', 'logout')?>"><?php echo Utilities::getLabel('M_LOG_OUT')?></a> 
              	<?php } else{?>
                    <a class="btn  primary-btn" href="<?php echo Utilities::generateUrl('user', 'account')?>"><?php echo Utilities::getLabel('M_LOGIN')?></a> <span class="or"><?php echo Utilities::getLabel('M_or')?></span> <a class="btn secondary-btn" href="<?php echo Utilities::generateUrl('user', 'account')?>"><?php echo Utilities::getLabel('M_SIGN_UP')?></a> 
              <?php }?>
             </div>
             <?php if (($is_buyer_logged) || ($is_front_user_logged == false) ):?>
            <div id="cart"> <a href="javascript:void(0)" id="cart_summary" class="click_trigger" ><i class="svg-icn">
              <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <path fill-rule="evenodd" clip-rule="evenodd" fill="#ff3a59" d="M70.1,11.7h-7l-7,21H25.7l-7-23.3c0,0-2.8-9.2-7-9.3h-7
	c0,0-5.5,1.9-4.7,4.7c0,0,0.3,6.7,9.3,2.3l11.7,35h39.6L70.1,11.7z"/>
                <path fill-rule="evenodd" clip-rule="evenodd" fill="#ff3a59" d="M29.2,46.3c3.2,0,5.8,2.6,5.8,5.8c0,3.2-2.6,5.8-5.8,5.8
	s-5.8-2.6-5.8-5.8C23.4,49,26,46.3,29.2,46.3z"/>
                <path fill-rule="evenodd" clip-rule="evenodd" fill="#ff3a59" d="M52.4,46.3c3.2,0,5.8,2.6,5.8,5.8c0,3.2-2.6,5.8-5.8,5.8
	s-5.8-2.6-5.8-5.8C46.5,49,49.2,46.3,52.4,46.3z"/>
              </svg>
              </i><span class="item_click_trigger" id="cart-total"><strong><?php echo Utilities::getLabel('M_Cart')?></strong> <span class="count_cart_items"><?php echo $cart_items?></span> <?php echo Utilities::getLabel('M_items')?> </span> </a>
              <ul id="list_cart_summary" class="dropdown-menu pull-right cart-drop">
	              <?php echo $cart_summary?>
              </ul>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </header>
  <div class="after-header"></div>
  <div class="clear"></div>
  <div class="mobile__overlay"></div>
  <div class="navpanel">
    <div class="fixed-container">
      <ul class="horizontal_links navigations">
      	<?php foreach($header_categories[0] as $hkey=>$hval): $subChild=0; $primaryCategory++;?>
        <?php if ($primaryCategory<7): $sub_cats_count = count($header_categories[$hval["category_id"]]); ?>	
        <li <?php if ($sub_cats_count>0) {?> class="navchild" <? } ?>> <a href="<?=Utilities::generateUrl('category','view',array($hval["category_id"]))?>"><?=Utilities::strip_javascript($hval["category_name"])?></a> 
          <?php if ($sub_cats_count>0) {?><span class="link__mobilenav"></span>
          <div class="subnav">
            <div class="subnav__wrapper addspace">
              <div class="fixed-container">
                <div class="subnav_row">
                  <ul class="sublinks">
                    <?php foreach($header_categories[$hval["category_id"]] as $subKey=>$subVal): $cntChild=0; $subChild++;
						if($subChild<9):
					?>
                    <li> <a href="<?=Utilities::generateUrl('category','view',array($subVal["category_id"]))?>"><?=$subVal["category_name"]?></a>
                      <ul>
                         <?php foreach($header_categories[$subVal["category_id"]] as $childKey=>$childVal): $cntChild++; if($cntChild<5): ?>
							<li><a href="<?=Utilities::generateUrl('category','view',array($childVal["category_id"]))?>"><?=Utilities::strip_javascript($childVal["category_name"])?></a></li>
    	               	<?php endif; endforeach;?>
						<?php if (count($header_categories[$subVal["category_id"]])>4):?>
		                    <li class="seemore"><a href="<?=Utilities::generateUrl('category','view',array($subVal["category_id"]))?>"><?=Utilities::getLabel('M_View_All')?></a></li>	
        		        <?php endif;?>
                      </ul>
                    </li>
                    <?php endif; endforeach;?>
                  </ul>
                  <?php if (count($header_categories[$hval["category_id"]])>8):?>
                  <a class="btn view-all" href="<?=Utilities::generateUrl('category','view',array($hval["category_id"]))?>"><?=Utilities::getLabel('M_View_all_categories')?></a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php } ?>
        </li>
        <?php endif; ?>
        <?php endforeach;?>
        <?php if (count($header_categories[0])>6): ?>
        <li class="navchild three-pin"> <a href="#" class="more"><span>More</span><i class="icn"></i></a> <span class="link__mobilenav"></span>
          <div class="subnav">
            <div class="subnav__wrapper addspace">
              <div class="fixed-container">
                <div class="subnav_row">
                  <ul class="sublinks">
                    <?php foreach($header_categories[0] as $subKey=>$subVal): $cntChild=0; $subxChild++; if($subxChild>6): ?>
                    <li> <a href="<?=Utilities::generateUrl('category','view',array($subVal["category_id"]))?>"><?=$subVal["category_name"]?></a>
                      <ul>
                        <?php foreach($header_categories[$subVal["category_id"]] as $childKey=>$childVal): $cntChild++; if($cntChild<5): ?>
						<li><a href="<?=Utilities::generateUrl('category','view',array($childVal["category_id"]))?>"><?=Utilities::strip_javascript($childVal["category_name"])?></a></li>
                       	<?php endif; endforeach;?>
						<?php if (count($header_categories[$subVal["category_id"]])>4):?>
        	            <li class="seemore"><a href="<?=Utilities::generateUrl('category','view',array($subVal["category_id"]))?>"><?=Utilities::getLabel('M_View_All')?></a></li>
            		    <?php endif;?>    
                      </ul>
                    </li>
                    <?php endif; endforeach;?>
                  </ul> 
                </div>
              </div>
            </div>
          </div>
        </li>
        <?php endif;?>
      </ul>
    </div>
  </div>
  
  <?php } else {?>
  <header id="myAccount">
    <div class="mainhead">
      <div class="top-head">
        <div class="fixed-container">
          <?php
		  	$mobile_logo_icon=Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON")!=""?Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON"):Settings::getSetting("CONF_FRONT_LOGO");
          ?>
          <div id="logo"><a href="<?php echo Utilities::getSiteUrl(); ?>"><span class="small-logo"><img src="<?php echo Utilities::generateUrl('image', 'mobile_icon_logo',array($mobile_logo_icon), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> <span class="medium-logo"><img src="<?php echo Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO")), CONF_WEBROOT_URL)?>" alt="<?php echo Settings::getSetting("CONF_WEBSITE_NAME")?>"/></span> </a> </div>
          <div class="mobile-element">
            <div class="app-style">
              <div class="myaccount"> <a href="#" class="clickme click_trigger" id="ct_1"> <span></span></a>
                <div class="listing-account-nav" id="list_ct_1">
                  <ul >
                  	<?php if ($is_seller_logged) {?>
                  	<?php if (is_null($user_details["shop_id"])){ ?>	
                    <li><a href="<?php echo Utilities::generateUrl('account', 'shop')?>"><?php echo Utilities::getLabel('M_Create_Your_Shop')?></a></li>
                    <?php } elseif ($user_details["shop_is_deleted"]==0) { ?>
                    <li><a target="_blank" href="<?php echo Utilities::generateUrl('shops', 'view',array($user_details["shop_id"]))?>"><?php echo Utilities::getLabel('M_View_Your_Shop')?></a></li>
                    <?php } ?>
                    <li><a href="<?php echo Utilities::generateUrl('account', 'product_form')?>"><?php echo Utilities::getLabel('M_List_Your_Product')?></a></li>
                    <?php }?>
                    <?php if ($is_buyer_logged) {?>
                    <li><a href="<?php echo Utilities::generateUrl('cart')?>"><?php echo Utilities::getLabel('M_Cart')?> (<span class="count_cart_items"><?php echo $cart_items?></span>)</a></li>
                    <?php } ?>
                    <li><a href="<?php echo Utilities::generateUrl('account', 'logout')?>"><?php echo Utilities::getLabel('M_LOG_OUT')?></a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <div class="right-side no-print">
          <?php if (($is_front_user_logged == true) && ($controller !="affiliate")){ ?> 
          
          <?php if ($is_seller_logged == true){ ?> 
          
          <?php if (is_null($user_details["shop_id"])){ ?>	
          <a href="<?php echo Utilities::generateUrl('account', 'shop')?>" class="btn secondary-btn"><i class="shop-icn"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/shop.svg"  alt="<?php echo Utilities::getLabel('M_Create_Your_Shop')?>"/></i><?php echo Utilities::getLabel('M_Create_Your_Shop')?></a>
          <?php } elseif ($user_details["shop_is_deleted"]==0 && $canAccessProductsArea) { ?>
          <a target="_blank" href="<?php echo Utilities::generateUrl('shops', 'view',array($user_details["shop_id"]))?>" class="btn secondary-btn"><i class="shop-icn"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/shop.svg"  alt="<?php echo Utilities::getLabel('M_View_Your_Shop')?>"/></i><?php echo Utilities::getLabel('M_View_Your_Shop')?></a>
          <?php } ?> 
          <a href="<?php echo Utilities::generateUrl('account', 'product_form')?>" class="btn secondary-btn"><i class="product-icn"><img src="<?php echo CONF_WEBROOT_URL?>images/retina/product.svg"  alt="<?php echo Utilities::getLabel('M_List_Your_Product')?>"/></i><?php echo Utilities::getLabel('M_List_Your_Product')?></a> 
          <?php }?>
          <a href="<?php echo Utilities::generateUrl('account', 'logout')?>" class="btn primary-btn"><?php echo Utilities::getLabel('M_LOG_OUT')?></a>
          <? } else {?>
          <a href="<?php echo Utilities::generateUrl('affiliate', 'logout')?>" class="btn primary-btn"><?php echo Utilities::getLabel('M_LOG_OUT')?></a>
          <? } ?>
 		
          <?php if (($is_buyer_logged) || ($is_front_user_logged == false) && (!$is_affiliate_logged)):?>	
            <div id="cart">
            <a href="javascript:void(0)" id="cart_summary" class="click_trigger"><span id="cart-total"><strong><?php echo Utilities::getLabel('M_Cart')?></strong> <span class="count_cart_items"><?php echo $cart_items?></span> <?php echo Utilities::getLabel('M_items')?>  </span> </a> <!--<a class="click_trigger after-arrow"  id="cart_summary"  href="javascript:void(0)"></a>-->
             	 <ul id="list_cart_summary" class="dropdown-menu pull-right cart-drop">
                 	<?php echo $cart_summary?>
              	</ul>
            </div>
           <?php endif; ?> 
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </header>
  <div class="after-header"></div>
  <?php } ?>
