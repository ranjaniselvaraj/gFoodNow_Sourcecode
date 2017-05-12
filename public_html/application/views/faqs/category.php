<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="sectionsearch">
        
      </div>
      <div class="fixed-container"><br>
        <br>
        <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Frequently_Asked_Questions')?><br>
<br>
</h1>
        <div class="faqcontainer">
          <div class="partLeft">
            <div class="sectiontoggle">
                <span class="spantxt"><?php echo Utilities::getLabel('L_FAQ_Categories') ?> </span><a href="javascript:void(0)" class="togglelink"></a>
            </div>
            <ul class="leftLinks">
                <?php foreach($faq_categories as $key=>$val):?>
                <li><a href="<?php echo Utilities::generateUrl('faqs', 'category',array($val["category_id"]))?>" <?php if($val["category_id"]==$faq_category["category_id"]):?> class="selectedlink" <?php endif; ?>><?php echo $val["category_name"]?></a></li>
                <?php endforeach;?>
            </ul>
           </div>
          <? $activeTab=0;?> 
          <div class="partRight">
                    		<h2><?php echo $faq_category["category_name"]?></h2>
                            <div class="cmsContainer">
								<?php foreach($faq_category["faqs"] as $fcat=>$fval): if ($faq==$fval['faq_id']) $activeTab=$fcat; ?>
                                <div class="contentrow">
                                    <span class="contentTitle tileicon accordianhead"><?php echo $fval["faq_question_title"]?></span>
                                    <div class="contentwrap accordiancontent">
                                        <p><?php echo nl2br($fval["faq_answer_brief"]);?></p>
                                    </div>
                                </div>
                                <?php endforeach;?>
                         </div>   
                    </div>
        </div>
      </div>
    </div>
    
  </div>
  
<script type="text/javascript">
 $(document).ready(function(){
		$('.togglelink').click(function() { $(this).toggleClass("active"); $('.leftLinks').slideToggle("600"); }); 
		$('.accordiancontent').hide(); //Hide/close all containers
		$('.accordianhead:eq(<?=$activeTab?>)').addClass('active').next().show(); //Add "active" class to first trigger, then show/open the immediate next container
		//$('.cmsContainer div:nth-child(2)').addClass('active').next().show();
		$('.accordianhead').click(function(){
		if( $(this).next().is(':hidden') ) { //If immediate next container is closed...
			$('.accordianhead').removeClass('active').next().slideUp(); //Remove all .acc_trigger classes and slide up the immediate next container
			$(this).toggleClass('active').next().slideDown(); //Add .acc_trigger class to clicked trigger and slide down the immediate next container
		}
		return false; //Prevent the browser jump to the link anchor
	});
	
});

</script> 
