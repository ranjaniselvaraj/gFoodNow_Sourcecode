<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo $user_details["user_name"]?>'s <?php echo Utilities::getLabel('L_Favorites')?></h3>
          <ul class="arrowTabs">
            <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"><?php echo Utilities::getLabel('M_Items')?></a></li>
            <li><a href="<?php echo Utilities::generateUrl('account', 'favorite_shops')?>"><?php echo Utilities::getLabel('L_Shops')?></a></li>
          </ul>
          <h3><?php echo Utilities::getLabel('L_Items_i_Love')?></h3>
          <?php if (count($products)>0):?>
          <div class="shop-list clearfix">
          	 <div id="ajax_message"></div>	
			 <span id="products-list"></span>
            <div class="gap"></div>
          </div>
          <?php else:?>
          		<div class="space-lft-right">
		          <div class="alert alert-info">
        		        <?php echo Utilities::getLabel('L_You_do_not_have_any_favorite_items')?>
		          </div>
              </div> 
          <?php endif; ?>
        </div>
        <!--right end--> 
      </div>
    </div>
  </div>
