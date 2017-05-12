<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="content slide">
	<div class="body clearfix">
		<div class="innerContainer">
			<div class="greyarea">
				<div class="fixed-container">
					<div class="sucessarea">
						<div class="container"> 
							<img src="<?=CONF_WEBROOT_URL?>images/success.png" alt="">
							<h2><span><?=Utilities::getLabel('L_Congratulations')?>!</span></h2>
							<div class="gap"></div>
							<?php echo Utilities::renderHtml($text_message)?>
							<div class="gap"></div>
						</div>
				</div>
			</div>
		</div>
	</div>
</div>
  