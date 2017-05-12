<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div id="body" class="body">
    <div id="main-area">
      <div class="bg-sell">
       <div class="fixed-container">
       <div class="seller-page"> <h2><?php echo Utilities::getLabel('L_Seller_Registration')?></h2>
          <div class="seller-row">
             <?php include CONF_THEME_PATH . $controller.'/left.php'; ?>
            <div class="seller-right">
              <div class="sell-form">
                <div class="seller-steps">
        	       <ul>
                   	<li class="compeleted"><a href="<?php echo Utilities::generateUrl('supplier', 'account')?>"><?php echo Utilities::getLabel('L_Signup_Details')?></a></li>
	               	<li <?php if ($supplier_profile_activation) :?> class="compeleted" <?php endif;?>><a href="<?php echo Utilities::generateUrl('supplier', 'account')?>"><?php echo Utilities::getLabel('L_Seller_Profile_Activation')?></a></li>
    	           	<li class="active"><a href="<?php echo Utilities::generateUrl('supplier', 'confirmation')?>"><?php echo Utilities::getLabel('L_Confirmation')?></a></li>
              	   </ul>
                </div>
                <div class="page-message">
	                  <h3><?php echo Utilities::getLabel('L_Thankyou')?></h3>
					  <p><?php echo Utilities::renderHtml($success_message); ?> </p>
					  <div class="aligncenter"><a class="btn" href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Back_to_home_page')?></a></div>
                </div>
              </div>
            </div>
          </div> </div>
        </div>
      </div>
    </div>
  </div>