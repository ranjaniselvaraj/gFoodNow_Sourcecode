<?php defined('SYSTEM_INIT') or die('Invalid Usage'); // printArray($user_details); ?>
<div class="profile-head clearfix no-print">
      <div class="fixed-container">
        <div class="threepin"> <a href="#" class="menu-dashboard click_trigger" id="ct_2"> <span></span></a> </div>
        <div class="fl">
          <div class="about-me">
            <div class="avatar"><img src="<?php echo Utilities::generateUrl('image', 'user', array($user_details['user_profile_image'],'SMALL'))?>" alt=""/></div>
            <div class="name"><?php echo $user_details["user_name"]?> <span><?php echo Utilities::displayNotApplicable(trim($user_details["user_city_town"].", ".$user_details["state_name"],", "),"")?></span></div>
            <? if ($user_details['shop_enable_cod_orders'] && Settings::getSetting("CONF_ENABLE_COD_PAYMENTS") && Settings::getSetting("CONF_ENABLE_COD_SELLER_NOTIFICATION") && (Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE")>$user_details['totUserBalance'])) { ?>
            <div class="gap_small"></div>
            <span class="smallalert alert-danger">
			<?php echo sprintf(Utilities::getLabel('M_COD_MIN_LIMIT_NOTIFICATION'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE")));?></span>
            <? } ?>
          </div>
        </div>
        <div class="fr">
          <?php if (!$is_advertiser_logged) {?> 	
          <div class="items-list">
            <ul>
              <li><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"><span><?php echo $user_details["favItems"]?></span><?php echo Utilities::getLabel('M_Favorite_Items')?></a></li>
              <? if ($buyer_supplier_tab=="S") { ?>
              <li><a href="<?php echo Utilities::generateUrl('account', 'publications')?>"><span><?php echo $user_details["publishedItems"]?></span><?php echo Utilities::getLabel('M_Published_Items')?></a></li>
			  <? } elseif ($buyer_supplier_tab=="B") { ?>   
		     	<li><a href="<?php echo Utilities::generateUrl('account', 'orders')?>"><span><?php echo $user_details["totUserOrderQtys"]?></span><?php echo Utilities::getLabel('M_Purchased_Items')?></a></li>
             <? } ?>
             
            </ul>
            
          </div>
          <?php } ?>
          <div class="liked-shop">
            <ul>
              <?php if (!$is_advertiser_logged) {?> 	
              <li><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                <g>
                  <g>
                    <defs>
                      <rect id="SVGID_1_" width="70" height="66.3"/>
                    </defs>
                    <clipPath id="SVGID_2_">
                      <use xlink:href="#SVGID_1_"  overflow="visible"/>
                    </clipPath>
                    <path clip-path="url(#SVGID_2_)" fill="#ffffff" d="M35,64.4l-4.9-4.9C11.9,43.4,0,32.5,0,19.2C0,8.4,8.4,0,19.2,0
			c6,0,11.9,2.8,15.7,7.3C38.8,2.8,44.8,0,50.7,0C61.6,0,70,8.4,70,19.2c0,13.3-11.9,24.2-30.1,40.2L35,64.4z M35,64.4"/>
                  </g>
                </g>
                </svg> <span class="tooltip-txt"><?php echo $user_details["favItems"]?> <?php echo Utilities::getLabel('M_Favorite_Items')?></span> </a></li>
              <?php } ?>
              <? if ($buyer_supplier_tab=="S") { ?>
              <li><a href="<?php echo Utilities::generateUrl('account', 'publications')?>"> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="-364 606 70 70" enable-background="new -364 606 70 70" xml:space="preserve">
                <g>
                  <g>
                    <path fill="#ffffff" d="M-310.2,606H-347c-9.6,3-14.8,7.4-14.8,7.4h66.4C-301.5,607.6-310.2,606-310.2,606z M-360.3,672.5h62.7
			v-48h-62.7V672.5z M-334.8,635.6c2.2,0,4.4,1.1,5.8,2.9c1.4-1.8,3.6-2.9,5.8-2.9c4,0,7.1,3.4,7.1,7.7c0,5.3-4.4,9.7-11.1,16.1
			l-1.8,2l-1.8-2c-6.7-6.5-11.1-10.8-11.1-16.1C-341.9,639-338.8,635.6-334.8,635.6z M-364,617.2v3.7h70.1v-3.7H-364z"/>
                  </g>
                </g>
                </svg> <span class="tooltip-txt"><?php echo $user_details["publishedItems"]?> <?php echo Utilities::getLabel('M_Published_Items')?></span></a></li>
             <? } elseif ($buyer_supplier_tab=="B") { ?>   
            	 <li><a href="<?php echo Utilities::generateUrl('account', 'orders')?>"> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="-364 606 70 70" enable-background="new -364 606 70 70" xml:space="preserve">
                <g>
                  <g>
                    <path fill="#ffffff" d="M-310.2,606H-347c-9.6,3-14.8,7.4-14.8,7.4h66.4C-301.5,607.6-310.2,606-310.2,606z M-360.3,672.5h62.7
			v-48h-62.7V672.5z M-334.8,635.6c2.2,0,4.4,1.1,5.8,2.9c1.4-1.8,3.6-2.9,5.8-2.9c4,0,7.1,3.4,7.1,7.7c0,5.3-4.4,9.7-11.1,16.1
			l-1.8,2l-1.8-2c-6.7-6.5-11.1-10.8-11.1-16.1C-341.9,639-338.8,635.6-334.8,635.6z M-364,617.2v3.7h70.1v-3.7H-364z"/>
                  </g>
                </g>
                </svg> <span class="tooltip-txt"><?php echo $user_details["totUserOrderQtys"]?> <?php echo Utilities::getLabel('M_Purchased_Items')?></span></a></li>
             <? } ?>
            </ul>
          </div>
        </div>
      </div>
    </div>