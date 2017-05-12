<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
?>
<div class="content slide">
    <div class="body clearfix">      
		  <div class="fixed-container">
			<div class="content ">
				<?php include 'rightpanelblog.php'; ?>
				<div class="col_left" id="archives-post-list"></div>
				<?php echo $frmArchives->getFormHtml();?>
			</div>
		</div>
	</div>
</div>
