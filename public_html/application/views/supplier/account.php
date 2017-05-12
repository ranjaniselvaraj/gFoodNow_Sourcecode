<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div id="body" class="body">
    <div id="main-area">
      <div class="bg-sell">
        <div class="fixed-container">
          <div class="seller-page"> <h2><?php echo Utilities::getLabel('L_Seller_Registration')?></h2>  <div class="seller-row">
            
            <?php include CONF_THEME_PATH . $controller.'/left.php'; ?>
            <div class="seller-right">
              <div class="sell-form">
                <div class="seller-steps">
                  <ul>
                	<li class="active"><a href="<?php echo Utilities::generateUrl('supplier', 'account')?>"><?php echo Utilities::getLabel('L_Signup_Details')?></a></li>
	                <li class=""><a href="#"><?php echo Utilities::getLabel('L_Seller_Profile_Activation')?></a></li>
    	            <li class=""><a href="#"><?php echo Utilities::getLabel('L_Confirmation')?></a></li>
        	      </ul>
                </div>
                <div class="steps-frm">
                  <?php echo $RegistrationFrm->getFormHtml();?>
                </div>
              </div>
            </div>
          </div> </div>
        </div>
      </div>
    </div>
  </div>
<script>
 $(document).ready(function($) {
	$('#check-password').strength({
				strengthClass: 'strength',
				strengthMeterClass: 'strength_meter',
				strengthButtonClass: 'button_strength',
				strengthButtonText: '<?php echo Utilities::getLabel('M_Show_Password')?>',
				strengthButtonTextToggle: '<?php echo Utilities::getLabel('M_Hide_Password')?>',
				strengthVeryWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_weak')?></p>',
				strengthWeakText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_weak')?></p>',
				strengthMediumText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_very_medium')?></p>',
				strengthStrongText: '<p><?php echo Utilities::getLabel('M_Strength')?>: <?php echo Utilities::getLabel('M_strong')?></p>'
			});
	});
 </script> 
