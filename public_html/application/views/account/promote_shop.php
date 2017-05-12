<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
           <h3><?php echo Utilities::getLabel('L_My_Promotions')?></h3>
          <ul class="arrowTabs">
                <li><a href="<?php echo Utilities::generateUrl('account', 'promote_product')?>"><?php echo Utilities::getLabel('L_Promote_Product')?></a></li>
                <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'promote_shop')?>"><?php echo Utilities::getLabel('L_Promote_Shop')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('account', 'promote_banner')?>"><?php echo Utilities::getLabel('L_Promote_Banner')?></a></li>
          </ul>
          <div class="fr right-elemnts"> <a href="<?php echo Utilities::generateUrl('account', 'promote')?>" class="btn small blue"><?php echo Utilities::getLabel('L_Back_to_Promotions')?></a> </div>
          <div class="space-lft-right">
	        <div class="wrapform">
				<?php echo $frmPromote->getFormHtml(); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
