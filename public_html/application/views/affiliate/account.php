<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="fixed-container">
        <div class="cmsContainer">
          <h3><?php echo Utilities::getLabel('L_Affiliate_Program')?></h3>
          <p><?php echo sprintf(Utilities::getLabel('L_Affiliate_Program_Description'),$affiliate_commission)?><br>
            <br>
            <?php echo Utilities::getLabel('L_Affiliate_Program_more_info')?>
            </p>
          <div class="gap"></div>
          <div class="row">
            <div class="col-sm-6">
              <div class="border-box">
                <h4><?php echo Utilities::getLabel('L_New_Affiliate')?></h4>
                <p><?php echo Utilities::getLabel('L_Currently_not_affiliate')?></p>
                <p><?php echo Utilities::getLabel('L_Click_below_create_affiliate_account')?><br>
                </p>
                <a class="btn btn-primary" href="<?php echo Utilities::generateUrl('affiliate', 'registration')?>"><?php echo Utilities::getLabel('L_Continue')?></a></div>
            </div>
            <div class="col-sm-6">
              <div class="border-box">
                <h4><?php echo Utilities::getLabel('L_Affiliate_Login')?></h4>
                <p><strong><?php echo Utilities::getLabel('L_Returning_Affiliate')?></strong></p>
                <?php //echo Utilities::displayHtmlForm($loginFrm) ?>
                <?php echo $loginFrm->getFormHtml();?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>    