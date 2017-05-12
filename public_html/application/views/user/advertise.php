<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="fixed-container">
        <div class="cmsContainer">
          <h3><?php echo Utilities::getLabel('L_Advertise_With_Us')?></h3>
          <p><?php echo nl2br(sprintf(Utilities::getLabel('L_Advertise_With_Us_Page_Description'),Settings::getSetting("CONF_WEBSITE_NAME")))?></p>
          <div class="gap"></div>
          <div class="row">
            <div class="col-sm-6">
              <div class="border-box">
                <h4><?php echo Utilities::getLabel('L_New_Advertiser')?></h4>
                <p><?php echo Utilities::getLabel('L_Currently_not_advertiser')?></p>
                <p><?php echo Utilities::getLabel('L_Click_below_create_advertiser_account')?><br>
                </p>
                <a class="btn btn-primary" href="<?php echo Utilities::generateUrl('user', 'advertiser_registration')?>"><?php echo Utilities::getLabel('L_Continue')?></a></div>
            </div>
            <div class="col-sm-6">
              <div class="border-box">
                <h4><?php echo Utilities::getLabel('L_Advertiser_Login')?></h4>
                <p><strong><?php echo Utilities::getLabel('L_Returning_Advertiser')?></strong></p>
                <?php echo $loginFrm->getFormHtml();?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>    