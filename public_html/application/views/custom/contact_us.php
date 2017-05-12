<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
   		<div class="pageBar">
            <div class="fixed-container">
                <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Contact_Us')?></h1>
            </div>
	    </div>
    	<div class="innerContainer">
		    <div class="greyarea">
			    <div class="fixed-container">
				    <div class="grid_1">
				    <?php 
					    $contact_content=str_replace('{SITEROOT}', CONF_WEBROOT_URL, $contact_content); 
					    echo Utilities::renderHtml($contact_content,true) 
				    ?>
			    	</div>
				    <div class="grid_2">
					    <h4><?php echo Utilities::getLabel('F_Drop_us_a_Line')?></h4>
				    	<?php echo $frm->getFormHtml();?>
			    	</div>
		    	</div>
	    	</div>
    	</div>
    </div>
</div>
<script src='https://www.google.com/recaptcha/api.js'></script>