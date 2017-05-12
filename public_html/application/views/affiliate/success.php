<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
        <div class="body clearfix">
        	<div class="innerContainer">
		        <div class="greyarea">
        			<div class="fixed-container">
				        <div class="sucessarea">
					        <div class="container"> 
					        <h3><span><?php echo Utilities::getLabel('L_AFFILIATE_ACCOUNT_CREATED')?></span></h3>
					        <div class="gap"></div>
						    <p><?php echo Utilities::renderHtml(nl2br($text_message))?></p>
					        <div class="gap"></div>
			        </div>
       				 <a href="<?php echo Utilities::getSiteUrl(); ?>" class="btn green"><?php echo Utilities::getLabel('L_Back_to_home')?></a> </div>
			        </div>
        		</div>
	        </div>
        </div>
    </div>
  