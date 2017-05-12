<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('M_LIVE_CHAT')?></h1>
        </div>
      </div>
      <div class="fixed-container">
        <div class="cmsContainer">
			<?php echo Utilities::renderHtml(Utilities::getLabel('M_LIVE_CHAT_STRING'),true); ?>
        </div>
      </div>
    </div>
</div>
