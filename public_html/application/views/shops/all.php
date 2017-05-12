<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Shops')?></h1>
        </div>
      </div>
      <div class="fixed-container">
        <div class="shop-page">
          <div class="shop-list clearfix shops_display_view">
            <?php echo Utilities::renderHtml($frmSearch);?>	
            <span id="shops-list"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
