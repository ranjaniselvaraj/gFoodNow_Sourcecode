<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Maintenance_Mode') ?> - <?php echo Utilities::getLabel('L_Enabled') ?></h1>
        </div>
      </div>
      <div class=" fixed-container">
        <div class="cmsContainer">
			<?php echo Utilities::renderHtml($maintenance_text,true); ?>
        </div>
      </div>
    </div>
  </div>
  