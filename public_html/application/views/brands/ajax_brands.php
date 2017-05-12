	<? if (count($brands)>0) { ?>
        <ul class="blackbullets">
          <?php foreach($brands as $key=>$val):?>	
          <li><a href="<?php echo Utilities::generateUrl('brands','view',array($val["brand_id"]))?>"><?php echo $val["brand_name"]?></a></li>
          <?php endforeach;?>
        </ul>
        <div class="clear"></div>
        <?php if ($page<$pages):?>
            <div class="aligncenter">
                <a href="<?php echo Utilities::generateUrl('brands','ajax_brands',array($page+1,$letter))?>" class="loadmore btn"><?php echo Utilities::getLabel('L_Load_More')?></a>
            </div>
        <?php else:?>    
            <a href="#" class=""></a>
        <?php endif;?>
	<? } else {?>
    		<div class="alert alert-info">
    	        	<?php echo Utilities::getLabel('L_Unable_to_find_any_record')?>
	        </div>
    <? }?>
<script type="text/javascript">
$(document).ready(function(){
		$('.scroll').jscroll({
	    	autoTrigger: false,
			loadingHtml: '<div class="center-display"><div id="loader" class="loader">Loading...</div></div>',
		});
	})
</script>	                            
            