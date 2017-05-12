<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="fixed-container">
        <div class="cmsContainer">
			<?php 
				$body=preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="http://'.$_SERVER['HTTP_HOST'].CONF_WEBROOT_URL.'$2$3',html_entity_decode($row["page_content"]));
				echo Utilities::renderHtml($product_setup_content,true);
			?>
        </div>
      </div>
    </div>
  </div>
  
