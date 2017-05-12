<?php defined('SYSTEM_INIT') or die('Invalid Usage'); // printArray($affiliate_details); ?>
<div class="profile-head clearfix no-print">
      <div class="fixed-container">
        <div class="threepin"> <a href="#" class="menu-dashboard click_trigger" id="ct_2"> <span></span></a> </div>
        <div class="fl">
          <div class="about-me">
            <div class="avatar"><img src="<?php echo Utilities::generateUrl('image', 'affiliate', array($affiliate_details['affiliate_profile_image'],'SMALL'))?>" alt=""/></div>
            <div class="name"><?php echo $affiliate_details["affiliate_name"]?> <span><?php echo Utilities::displayNotApplicable(trim($affiliate_details["affiliate_city"].", ".$affiliate_details["state_name"],", "))?></span></div>
          </div>
        </div>
        
      </div>
    </div>