<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
        <div class="body clearfix">
        	<div class="innerContainer">
		        <div class="greyarea">
        			<div class="fixed-container">
				        <div class="sucessarea">
					        <div class="container"> 
                            <img src="<?php echo CONF_WEBROOT_URL?>images/success.png" alt="<?php echo Utilities::getLabel('L_Payment_Success')?>">
					        <h2><span><?php echo Utilities::getLabel('L_Congratulations')?>!</span></h2>
					        <div class="gap"></div>
						        <?php echo Utilities::renderHtml($text_message)?>
					        <div class="gap"></div>
			        	 </div>
	       				 <?php if(!$hide_header_footer){?>
						    <a href="<?php echo Utilities::getSiteUrl(); ?>" class="btn green"><?php echo Utilities::getLabel('L_Continue_Shopping')?></a> 
						 <?php }?>
						</div>
                        
                        
                        <? if (count($smart_recommended_products)>0) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Recommended_Products_For_You') ?></h3>
    	        </div>
                <div class="shop-list products-carousel <? if (count($smart_recommended_products)<6):?>less_items<?php endif; ?>">
		            <?php  foreach ($smart_recommended_products as $product) { 
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
            </div>
        <? } ?>
                        
			        </div>
                    
                    
                    
        		</div>
	        </div>
        </div>
    </div>
  