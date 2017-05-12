<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('lbl_Forgot_Password')?></h1>
        </div>
      </div>
      <div class=" fixed-container">
       <div class="sectionEnter">
          <h3><span><?php echo Utilities::getLabel('lbl_Enter_the_email_address_your_account')?></span> </h3>
          <div class="wrapsmall">
            <div class="borderframe">
	              <?php echo $frm->getFormHtml(); ?>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<script src='https://www.google.com/recaptcha/api.js'></script>