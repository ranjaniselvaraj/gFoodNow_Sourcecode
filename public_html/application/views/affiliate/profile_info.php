<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <h3><?php echo Utilities::getLabel('L_Account_Information')?></h3>
          <div class="space-lft-right">
            <div class="wrapform">
	            <?php echo $frmProfileImage->getFormHtml(); ?>
                <br/>
				<?php echo $frm->getFormHtml(); ?>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
$(document).ready(function(){
	loadStates(document.getElementById('ua_country'), <?php echo intval($frm->getField('affiliate_state')->value); ?>);
});
</script> 