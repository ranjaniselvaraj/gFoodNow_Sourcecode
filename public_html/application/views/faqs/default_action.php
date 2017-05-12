<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
    	<div class="sectionsearch">
	    </div>
   		<div class="fixed-container">
    	<br>
    	<br>
	    <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Browse_by_Topic') ?></h1>
	    <div class="blockrow">
    		<?php foreach($faq_categories as $fcat=>$fval):?>	
			    <div class="listblock">
				    <h4><?php echo $fval["category_name"]?></h4>
					    <ul class="listing_ticks">
						    <?php foreach($fval["faqs"] as $qcat=>$qval):?>
							    <li><a href="<?php echo Utilities::generateUrl('faqs', 'category',array($fval["category_id"],$qval["faq_id"]))?>"><?php echo $qval["faq_question_title"]?></a></li>
						    <?php endforeach;?>
					    </ul>
				<span class="texthighlight"><img src="<?php echo CONF_WEBROOT_URL?>images/articale.png" alt="<?php echo $fval["category_name"]?>"> <span><?php echo $fval["totRecords"]?> <?php echo Utilities::getLabel('L_Questions') ?></span></span> <a href="<?php echo Utilities::generateUrl('faqs', 'category',array($fval["category_id"]))?>" class="blueButton"><?php echo Utilities::getLabel('L_View_All') ?></a> </div>
			<?php endforeach?> 
	    	</div>
    	</div>
    </div>
</div>
