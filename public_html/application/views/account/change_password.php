<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo Utilities::getLabel('L_Change_Password')?></h3>
          <ul class="arrowTabs">
	             <li><a href="<?php echo Utilities::generateUrl('account', 'profile_info')?>"><?php echo Utilities::getLabel('L_Personal_Information')?></a></li>
                 <li><a href="<?php echo Utilities::generateUrl('account', 'bank_info')?>"><?php echo Utilities::getLabel('L_Bank_Account_Info')?></a></li>
                 <?php if (!$is_social_user_logged):?> 	
                 <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'change_password')?>"><?php echo Utilities::getLabel('M_Change_Password')?></a></li>
                 <?php endif;?>
                 <li><a href="<?php echo Utilities::generateUrl('account', 'change_email')?>"><?php echo Utilities::getLabel('M_Change_Email')?></a></li>
          </ul>
          <div class="space-lft-right">
	        <div class="wrapformChangePassword">
				<?php echo $frmPwd->getFormHtml(); ?>
            </div>
          </div>
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