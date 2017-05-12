<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>	
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
        <div class="dashboard">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
          <div class="data-side">
          	<?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
            <h3><?php echo Utilities::getLabel('L_My_Addresses')?></h3>
            <ul class="arrowTabs">
              <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'addresses')?>"><?php echo Utilities::getLabel('L_Address_List')?></a></li>
              <li><a href="<?php echo Utilities::generateUrl('account', 'address_form')?>"><?php echo Utilities::getLabel('L_Add_Address')?></a></li>
            </ul>
            
            <div class="space-lft-right">
              <?php if (count($addresses)>0):?>	  
              <div class="all-address">
              	<?php $i = 0; foreach($addresses as $address){ ?>
                <div class="repeat <?php echo (($address['ua_is_default'])?'selected':''); ?>">
                  <div class="icon"><img width="29" height="29" src="<?php echo CONF_WEBROOT_URL?>images/home-icon.png" alt=""></div>
                  <div class="address_text">
                  <p><strong><?php echo $address['ua_name']?> </strong><br>
                    <?php echo ((strlen($address['ua_address1']) > 0)?$address['ua_address1']:'') .((strlen($address['ua_address2']) > 0)?'<br/>'.$address['ua_address2']:'') . ((strlen($address['ua_city']) > 0)?'<br/>'.$address['ua_city'] . ', ':'') . $address['ua_zip']; ?><br><?php echo $address['state_name']; ?>, <?php echo $address['country_name']; ?><br>
                    T: <?php echo $address['ua_phone']; ?></p>
                  </div>  
                  <div class="clear"></div>
                  <div class="actions"><a class="btn small" href="<?php echo Utilities::generateUrl('account', 'address_form',array($address["ua_id"]))?>"><?php echo Utilities::getLabel('L_Edit')?></a> <a class="btn small" onclick="return(confirm('<?php echo Utilities::getLabel('L_Are_you_sure_delete')?>'));" href="<?php echo Utilities::generateUrl('account', 'delete_address',array($address["ua_id"]))?>"><?php echo Utilities::getLabel('L_Delete')?></a> <a class="btn small" href="<?php echo Utilities::generateUrl('account', 'default_address',array($address["ua_id"]))?>"><?php echo ($address['ua_is_default'])?Utilities::getLabel('L_Default_Address'):Utilities::getLabel('L_Set_as_default'); ?></a></div>
                </div>
                <?php }?>
              </div>
              <?php else:?>
                     <div class="alert alert-info">
                        <?php echo sprintf(Utilities::getLabel('L_We_are_unable_to_find_record'),'<a href="'.Utilities::generateUrl('account', 'address_form').'">'.Utilities::getLabel('L_click_here').'</a>')?>
                    </div>
            	<?php endif;?>
            </div>
          </div>
        </div>
      </div>
  </div>
      