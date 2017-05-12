<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix"> 
    	<?php echo str_replace("images/",CONF_WEBROOT_URL."images/",Utilities::renderHtml($become_seller_content));?>
    </div>
  </div>
