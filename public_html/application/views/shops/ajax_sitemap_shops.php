	<? if (count($shops)>0) { ?>
		<ul class="blackbullets">
		  <?php  foreach($shops as $key=>$val):?>	
		  	<li><a href="<?php echo Utilities::generateUrl('shops','view',array($val["shop_id"]))?>"><?php echo $val["shop_name"]?></a></li>
		  <?php  endforeach;?>
		</ul>
		<div class="clear"></div>
		<?php  if ($page<$pages):?>
		<div class="aligncenter">
    		<a href="<?php echo Utilities::generateUrl('shops','ajax_sitemap_shops',array($page+1,$letter))?>" class="loadmore btn"><?php echo Utilities::getLabel('L_Load_More')?></a>
		</div>
	<?php  else:?>    
		<a href="#" class=""></a>
	<?php  endif;?>
   	<? } else {?> 
    		<div class="alert alert-info">
            	<?php echo Utilities::getLabel('L_Unable_to_find_any_record')?>
            </div>
    <? }?>
<script type="text/javascript">
$(document).ready(function(){
		$('.scroll').jscroll({
	    	autoTrigger: false,
			loadingHtml: '<div class="center-display"><img src="<?php echo CONF_WEBROOT_URL?>images/loader.jpg" alt="<?php echo Utilities::getLabel('L_Loading')?>" /></div>',
		});
	})
</script>	                            
            