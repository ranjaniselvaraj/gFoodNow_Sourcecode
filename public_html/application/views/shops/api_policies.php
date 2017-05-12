<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php include CONF_THEME_PATH . 'payment-header.php';?>
<div>
    <div class="body clearfix">
      
      <div class="fixed-container">
        <div class="shop-page">
           <? if ($shop['shop_description']!="") {?>  
			<div class="description"><div class="more"><?php echo $shop['shop_description']?></div></div>
           <? } ?>
          
          <div class="cmsPolicy">
            <h4><?php echo sprintf(Utilities::getLabel('L_Shop_Policies'),$shop["shop_name"])?></h4>
            <div class="cmsContainer colscontainer">
			  <?php  if ($shop["shop_payment_policy"]!="") {?> 	
              <div class="rowcontent">
                <h5><?php echo Utilities::getLabel('L_Payment')?></h5>
                <p><?php echo nl2br($shop["shop_payment_policy"])?></p>
              </div>
              <?php } ?>
              <?php  if ($shop["shop_delivery_policy"]!="") {?> 	
              <div class="rowcontent">
                <h5><?php echo Utilities::getLabel('L_Shipping')?></h5>
                <p><?php echo nl2br($shop["shop_delivery_policy"])?></p>
              </div>
              <?php  } ?>
              <?php  if ($shop["shop_refund_policy"]!="") {?> 	
              <div class="rowcontent">
                <h5><?php echo Utilities::getLabel('L_Refunds_Exchanges')?></h5>
                <p><?php echo nl2br($shop["shop_refund_policy"])?></p>
              </div>
              <?php  } ?>
              <?php  if ($shop["shop_additional_info"]!="") {?> 	
              <div class="rowcontent">
                <h5><?php echo Utilities::getLabel('L_Additional_Policies_FAQs')?></h5>
                <p><?php echo nl2br($shop["shop_additional_info"])?></p>
              </div>
              <?php  } ?>
              <?php  if ($shop["shop_seller_info"]!="") {?> 	
              <div class="rowcontent">
                <h5><?php echo Utilities::getLabel('L_Seller_Information')?></h5>
                <p><?php echo nl2br($shop["shop_seller_info"])?></p>
              </div>
              <?php  } ?>
              <div class="rowcontent"> <span class="smallItalicText"><?php echo Utilities::getLabel('L_Last_updated')?> <?php echo Utilities::formatDate($shop["shop_update_date"])?></span> </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
