<?php defined('SYSTEM_INIT') or die('Invalid Usage');?>
<div>
    <div class="body clearfix">      
      <div class="fixed-container">
        <div class="content ">
            <?php include 'rightpanelblog.php'; ?>
            <div class="col_left" id="category-post-list"></div>
            <?php echo $frmCategory->getFormHtml();?>
        </div>
      </div>
    </div>
  </div>