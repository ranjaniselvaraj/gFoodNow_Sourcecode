<?php defined('SYSTEM_INIT') or die('Invalid Usage'); 
$shop_vendor_location = ((strlen($shop['shop_owner_state_name']) > 0)?$shop['shop_owner_state_name']:'').((strlen($shop['shop_owner_country_name']) > 0)?', '.$shop['shop_owner_country_name']:'');?>
<div>
    <div class="body clearfix">
      <div class="profile-head clearfix">
        <div class="fixed-container">
          <div class="fl">
            <div class="about-me">
              <div class="avatar"><img src="<?php echo Utilities::generateUrl('image','user',array($shop["shop_owner_profile_image"],'SMALL'))?>" alt="<?php echo $shop["shop_owner_username"]?>"/></div>
              <div class="name"><?php echo $shop["shop_owner_username"]?> <span><?php echo Utilities::displayNotApplicable($shop_vendor_location)?></span></div>
            </div>
          </div>
          <div class="fr">
            <div class="items-list">
              <ul>
                <li><a href="<?php echo Utilities::generateUrl('shops','view',array($shop["shop_id"]))?>"><span><?php echo $shop["totProducts"]?></span><?php echo Utilities::getLabel('M_Total_Items')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('shops','reviews',array($shop["shop_id"]))?>"><span><?php echo $shop["totReviews"]?></span><?php echo Utilities::getLabel('M_Total_Reviews')?></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      
      <div class="fixed-container">
        <div class="shop-header">
          <div class="img"><img src="<?php echo Utilities::generateUrl('image','shop_banner',array($shop["shop_banner"],'LARGE'))?>" width="1200" height="361" alt="<?php echo $shop["shop_name"]?>"/></div>
          <div class="shop-left">
            <div class="brand-icon"><img src="<?php echo Utilities::generateUrl('image','shop_logo',array($shop["shop_logo"],'THUMB'))?>" width="97" height="97" alt="<?php echo $shop["shop_name"]?>"/></div>
            <div class="brand-name"><?php echo $shop["shop_name"]?> <span>(<?php echo Utilities::getLabel('M_Opened_on')?> <?php echo Utilities::formatDate($shop["shop_date"])?>)</span></div>
            <div class="links-row"> <a id="shop_<?php echo $shop["shop_id"]?>" class="btn gray favShop <? if ($shop["favorite"]): echo 'active'; endif;?>"><i><img src="<?php echo CONF_WEBROOT_URL?>images/heart-white.png"  alt="<?php echo Utilities::getLabel('M_Favorite_Shop')?>"/></i><?php echo Utilities::getLabel('M_Favorite_Shop')?></a> <a href="<?php echo Utilities::generateUrl('shops','send_message',array($shop["shop_id"]))?>" class="btn blue"><i><img src="<?php echo CONF_WEBROOT_URL?>images/email-white.png"   alt="<?php echo Utilities::getLabel('M_Send_Message')?>"/></i><?php echo Utilities::getLabel('M_Send_Message')?></a>
              <?php  if (Settings::getSetting("CONF_ALLOW_REVIEWS")):?>
              <div class="rating">
                <ul class="rating">
                  <?php for($j=1;$j<=5;$j++){ ?>	
                  <li class="<?php echo $j<=round($shop["shop_rating"])?"active":"in-active" ?>"> 
                    <svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                    <g>
                      <path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" 
                      fill="<?php echo $j<=round($shop["shop_rating"])?"#ff3a59":"#474747" ?>" />
                    </g>
                    </svg> </li>
                  <?php } ?>  
                </ul>
                <p><?php echo $shop["shop_rating"] ?> <?php echo Utilities::getLabel('M_out_of')?> 5</p>
              </div>
              <?php  endif; ?>
            </div>
          </div>
          <div class="shop-right">
            <div class="social-list">
              	<div class="sharewrap">
                	<span class='st_sharethis_large' displayText='ShareThis'></span>
                    <span class='st_facebook_large' displayText='Facebook'></span>
                    <span class='st_twitter_large' displayText='Tweet'></span>
                    <span class='st_pinterest_large' displayText='Pinterest'></span>
                    <span class='st_whatsapp_large' displayText='WhatsApp'></span>
                    <span class='st_email_large' displayText='Email'></span>
                 </div>
            </div>
            <div class="short-links">
              <ul >
                <li> <a href="<?php echo Utilities::generateUrl('shops','who_favorite_shop',array($shop["shop_id"]))?>"><?php echo Utilities::getLabel('L_See_who_favorite_this')?></a> </li>
                <li> <a href="<?php echo Utilities::generateUrl('shops', 'report',array($shop["shop_id"]))?>"> <?php echo Utilities::getLabel('L_Report_this_shop')?></a> </li>
                <li> <a href="<?php echo Utilities::generateUrl('shops','policies',array($shop["shop_id"]))?>"> <?php echo Utilities::getLabel('M_Policies')?> </a> </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="shop-page">
        	
           <? if ($shop['shop_description']!="") {?>  
			<div class="description"><div class="more"><?php echo $shop['shop_description']?></div></div>
           <? } ?>
          	
       	  <?php include CONF_THEME_PATH . 'shop_leftpanel.php'; ?>
          
          <div class="right-panel">
          	<?php if ($category_info["category_name"]) { ?>
            <div class="shopbreadcrumb">
                <div class="fixed-container">
                	<ul>
		                <li><a href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Home')?></a></li>
        		        <li><a href="<?php echo Utilities::generateUrl('shops','view',array($shop["shop_id"]))?>"><?php echo $shop["shop_name"]?></a></li>
                		<?php  foreach($category_structure as $catKey=>$catVal): ?>
			                <li><a href="<?php echo Utilities::generateUrl('shops','view',array($shop["shop_id"],$catVal["category_id"]))?>"><?php echo $catVal["category_name"]?></a></li>
            		    <?php  endforeach;?>
		                <li><?php echo $category_info["category_name"]?></li>
        	        </ul>
                </div>
            </div>
            <?php } ?>
            
            <div class="shop-list clearfix">
                	<?php echo $frmSearch->getFormHtml();?>	
	                <span id="products-list"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
<!--wrapper end here-->
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "c1ac1329-15e5-4379-aa5c-30b5671f7265", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>