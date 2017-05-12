<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . 'affiliate_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . 'affiliate_leftpanel.php'; ?>
        <div class="data-side">
          <h3><?php echo Utilities::getLabel('L_Payment_Information')?></h3>
          <div class="space-lft-right">
            <div class="wrapform">
				<?php echo $frm->getFormHtml(); ?>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
<script type="text/javascript">
$(document).ready(function(){
	$('input[name=\'affiliate_payment\']').on('change', function() {
		$('.payment').parent().parent().hide();
		$('div [lang=\'payment-'+this.value+'\']').parent().parent().show();
	});
	$('input[name=\'affiliate_payment\']:checked').trigger('change');
	
});
</script>