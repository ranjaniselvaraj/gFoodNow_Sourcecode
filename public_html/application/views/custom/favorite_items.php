<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="profile-head clearfix">
        <div class="fixed-container">
          <div class="fl">
            <div class="about-me">
              <div class="avatar"><img src="<?php echo Utilities::generateUrl('image','user',array($user_details["user_profile_image"],'SMALL'))?>" alt="<?php echo $user_details["user_username"]?>"/></div>
              <div class="name"><?php echo $user_details["user_username"]?> <span><?php echo $user_details["state_name"]?>, <?php echo $user_details["country_name"]?></span></div>
            </div>
          </div>
          <div class="fr">
            <div class="items-list">
              <ul>
                <li><a href="<?php echo Utilities::generateUrl('custom','favorite_items',array($user_details["user_id"]))?>"><span><?php echo $user_details["favItems"]?></span><?php echo Utilities::getLabel('L_Favorite_Items')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('custom','favorite_shops',array($user_details["user_id"]))?>"><span><?php echo $user_details["favShops"]?></span><?php echo Utilities::getLabel('L_Favorite_Shops')?></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="fixed-container">
		<div class="shop-page">
          <?php if (count($products)>0):?>	
   		  <div id="ajax_message"></div>
          <div class="shop-list clearfix">
          	<?php foreach ($products as $product) { ?>
					<?php include CONF_THEME_PATH . 'common/product_thumb_view.php'; ?>
			<?php } ?>
          </div>
          <?php else:?>
                  <div class="notification informationfullwidth">
                        <p><strong><?php echo Utilities::getLabel('L_Information')?></strong><br/>
                            <?php echo Utilities::getLabel('L_You_not_have_record')?>
                        </p>
                      </div>
                <?php endif;?>
        </div>
      </div>
    </div>
  </div>
  
