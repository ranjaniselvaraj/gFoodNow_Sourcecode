<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
   		<div class="pageBar">
            <div class="fixed-container">
                <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Subscription_Packages')?></h1>
            </div>
	    </div>
    	<div class="innerContainer">
		    <div class="greyarea">
			    <div class="fixed-container">
				   <div class="packages-section">
						<div class="packages-box clearfix">
							<? include CONF_THEME_PATH . 'packages_tiles.php';  ?>
						</div>
					</div>
		    	</div>
	    	</div>
    	</div>
    </div>
</div>
