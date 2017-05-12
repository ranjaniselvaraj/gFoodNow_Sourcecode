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
	               	<li class="active"><a href="<?php echo Utilities::generateUrl('supplier', 'account')?>"><?php echo Utilities::getLabel('L_Seller_Profile_Activation')?></a></li>
    	           	<li class=""><a href="#"><?php echo Utilities::getLabel('L_Confirmation')?></a></li>
              	   </ul>
                </div>
                <div class="steps-frm">
	                  <h4><?php echo Utilities::getLabel('L_PLEASE_SUBMIT_YOUR_BUSINESS_INFO')?></h4> 	
             		 <?php echo $supplierFrm->getFormHtml();?>
                </div>
              </div>
            </div>
          </div> </div>
        </div>
      </div>
    </div>
  </div>