<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Resend_verification_code')?></h1>
        </div>
      </div>
      <div class=" fixed-container">
       <div class="sectionEnter">
          <div class="wrapsmall">
            <div class="borderframe">
              <div class="gap"></div>
              <?php echo $frm->getFormHtml(); ?>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<script src='https://www.google.com/recaptcha/api.js'></script>