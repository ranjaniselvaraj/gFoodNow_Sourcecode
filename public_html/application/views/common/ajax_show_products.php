<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
	<div class="ppc-campaign">
		<?php if ($total_records>0) { 
				foreach ($products as $product) { 
					 include CONF_THEME_PATH . 'common/product_thumb_view.php'; 
				} 
		}else{ ?>
			<div class="aligncenter">
          		<div class="no-product">
            		<div class="rel-icon"><img src="<?=CONF_WEBROOT_URL?>images/empty_shopping_bag.png" alt=""></div>
        			<div class="no-product-txt"> <span><?php echo Utilities::getLabel('L_We_could_not_find_matches')?> </span> <?php echo Utilities::getLabel('L_Try_different_keywords_filters')?></div>
	          </div>
        	</div>
		<?php }?>
    </div>
<script type="text/javascript">
$(document).ready(function() { 
		setTimeout(function(){ $(".product_load_later").each(function() {
			$(this).attr('src',$(this).attr('data-img-src'));
			}); 
		}, 2000);
		ppc_track_impressions();
	})
	
</script>				