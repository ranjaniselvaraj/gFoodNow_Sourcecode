<?php defined('SYSTEM_INIT') or die('Invalid Usage');  ?>
	
<div>
	<div class="body clearfix">
    	
        
        
      <div class="fixed-container"> 
        
        
        <div class="main-slider">  
       	<?php if ((!empty($promotion_header_banners) && count($promotion_header_banners)>1) || (!empty($home_page_elements["slides"]) && count($home_page_elements["slides"])>1)) {?>
        <div class="new-slider-control"> <a class="ripplelink  prev-btn"><i class="icn-svg"><svg xml:space="preserve" enable-background="new 0 0 7 12" viewBox="0 0 7 12" height="12px" width="7px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg">
          <path d="M6.641,12L0,5.915L6.641,0L7,0.507L1.256,5.915L7,11.493L6.641,12z" clip-rule="evenodd" fill-rule="evenodd"/>
          </svg> </i></a> <a class="ripplelink  next-btn"><i class="icn-svg"> <svg xml:space="preserve" enable-background="new 0 0 7 12" viewBox="0 0 7 12" height="12px" width="7px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg">
          <path d="M0.359,12L7,5.915L0.359,0L0,0.507l5.743,5.408L0,11.493L0.359,12z" clip-rule="evenodd" fill-rule="evenodd"/>
          </svg> </i></a> </div>
          <?php } ?>
        <div class="new-slider ppc-campaign">
	          <?php if (empty($promotion_header_banners)) {
					foreach($home_page_elements["slides"] as $sn=>$val):?>	
		          		<div class="item">
                        <?php if (!empty($val["slide_url"])) { ?>
		    	    	<a href="<?php echo $val["slide_url"]?>" target="<?php echo ($val["slide_link_newtab"]==1)? '_blank':'' ?>">
           			 	<?php } ?>
                        <img class="load_later" src="<?php echo Utilities::generateUrl('image','slide',array($val["slide_image_path"],'NORMAL'))?>" data-img-src="<?php echo Utilities::generateUrl('image','slide',array($val["slide_image_path"]))?>" alt="" /> 
                        <?php if (!empty($val["slide_url"])) { ?> </a><?php }?>
                        </div>
        		  	<?php endforeach;?>
	              	<?php } else {
	              	 foreach($promotion_header_banners as $sn=>$val):?>
    	      			<div data-attr-id="<?php echo $val["promotion_id"]?>" class="item <? if ($val["promotion_id"]>0):?>thumb_impression thumb_click<?php endif;?>" > 		<?php if (!empty($val["promotion_banner_url"])) { ?>
		    	    	<a target="<?php echo $val["promotion_banner_target"];?>" href="<?php echo Utilities::generateUrl('promotions','track_click',array($val["promotion_id"]))?>" >
           			 <?php } ?> 
                     	<img class="load_later" src="<?php echo Utilities::generateUrl('image','promotion_banner',array($val["promotion_banner_file"],'NORMAL'))?>" data-img-src="<?php echo Utilities::generateUrl('image','promotion_banner',array($val["promotion_banner_file"]))?>"  alt="" /> 
					 <?php if (!empty($val["promotion_banner_url"])) { ?> </a><?php }?></div>
        	  		<?php endforeach;
				 } ?> 
        </div>
        
        
        </div>
        <div class="gap"></div>
    	<div id="ajax_message"></div>
		<? 
		if ((count($featured_products)>0) && !empty($featured_products)) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Featured_Products') ?></h3>
    	        </div>
                <div class="shop-list home-list ppc-campaign">
		            <?php  foreach ($featured_products as $product) {  
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
            </div>
        <? } ?>
        
        <? if ((count($ppc_products)>0) && !empty($ppc_products)) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Settings::getSetting("CONF_PPC_PRODUCTS_HOME_PAGE_CAPTION") ?></h3>
    	        </div>
                <div class="shop-list home-list ppc-campaign">
		            <?php  foreach ($ppc_products as $product) {  
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
            </div>
        <? } ?>
        
        <? if ((count($ppc_shops)>0) && !empty($ppc_shops)) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Settings::getSetting("CONF_PPC_SHOPS_HOME_PAGE_CAPTION") ?></h3>
    	        </div>
                <div class="shop-list clearfix shops_display_view ppc-campaign">
		            <?php foreach($ppc_shops as $sn=>$row): $incshop++;  ?>
			            <?php  include CONF_THEME_PATH . 'common/shop_thumb_view.php'; ?>
            		<?php  endforeach;  ?>
        	    </div>
            </div>
        <? } ?>
        
        <?php foreach($home_page_elements["collections"] as $sn=>$val): $collection++;
		
		if (($val["collection_type"]=="C") && count($val["collection_categories"])>0): $incc = 0;?>
        <div class="section category-list">
          <h3><?php echo $val["collection_display_title"]?></h3>
          <div class="main-category">
            <div class="category-nav">
              <ul class="resp-tabs-list">
       	       <?php foreach($val["collection_categories"] as $cat=>$catval): $incc++; if ($incc<=$val["collection_primary_records"]) {?>
	        	<li class="ripplelink collection_category" id="<?php echo $val["collection_id"]."-".$catval["id"];?>"><?php echo Utilities::renderHtml($catval["short_name"]);?></li>
                <?php } endforeach;?>
              </ul>
            </div>
            <div class="resp-tabs-container shop-list collection_box">
                <?php $inc=0; foreach($val["collection_categories"] as $cat=>$catval): $inc++; if ($inc<=$val["collection_primary_records"]) {?>
		           <div class="product-layout ppc-campaign" id="div_ajax_products_<?php echo $val["collection_id"]."-".$catval["id"];?>">
                   <?php $products=$catval["products"]; if (!empty($catval["products"])) {
					 foreach ($products as $product) { 
							include CONF_THEME_PATH . 'common/product_thumb_view.php'; 
						} 
					}else{ ?>
					<div class="collection-box"> 
						<?php echo Utilities::getLabel('L_Unable_to_find_any_record')?>
					</div> 
					<?php }?>
                   </div>
                 <?php } endforeach; $inc=0;?>
            </div>
          </div>
        </div>
        	<?php elseif (($val["collection_type"]=="S") && (count($val["collection_shops"])>0)): ?>
            <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo $val["collection_display_title"]?></h3>
    	        </div>
                <div class="shop-list clearfix shops_display_view ppc-campaign">
		            <?php $incshop=0; $collection_primary_records = $val["collection_primary_records"]; foreach($val["collection_shops"] as $sn=>$row): $incshop++; if ($incshop <= $collection_primary_records) { ?>
			            <?php  
						include CONF_THEME_PATH . 'common/shop_thumb_view.php'; ?>
            		<?php  } endforeach;  ?>
        	    </div>
            </div>
            <div class="gap clear"></div>
        	<?php elseif (($val["collection_type"]=="P") && (count($val["collection_products"])>0)): ?>
            <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo $val["collection_display_title"]?></h3>
    	        </div>
                <!--products-carousel-->
                <div class="shop-list home-list ppc-campaign"> 
		            <?php $products=$val["collection_products"];  $inc=0;
        				    foreach ($products as $product) { $inc++; if ($inc <= $val["collection_primary_records"]) { 
					            include CONF_THEME_PATH . 'common/product_thumb_view.php';
				            }
		            }  ?>
        	    </div>
            </div>
	       <?php endif; 
		endforeach;?>
        
        <?  
		if (count($recently_viewed_products)>0) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Recently_Viewed_Products') ?></h3>
    	        </div>
                <div class="shop-list products-carousel ppc-campaign <? if (count($recently_viewed_products)<6):?>less_items<?php endif; ?>">
		            <?php  foreach ($recently_viewed_products as $product) {  
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
            </div>
        <? } ?>
        
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
        
        
        <?php if (!empty($promotion_bottom_banners) && count($promotion_bottom_banners)>0 ) {?>
        		<div class="section">
          			<div class="adds ppc-campaign">
        		<?php foreach($promotion_bottom_banners as $sn=>$val) {?>
                	<div class="grid-item <? if ($val["promotion_id"]>0):?>thumb_impression thumb_click<?php endif;?>" data-attr-id="<?php echo $val["promotion_id"]?>">
                			<a class="x" target="<?php echo $val["promotion_banner_target"];?>" href="<?php echo Utilities::generateUrl('promotions','track_click',array($val["promotion_id"]))?>"  >
           					<img class="load_later  img-responsive" src="<?php echo Utilities::generateUrl('image','promotion_banner',array($val["promotion_banner_file"],'NORMAL'))?>" data-img-src="<?php echo Utilities::generateUrl('image','promotion_banner',array($val["promotion_banner_file"]))?>" width="300" height="300"  />
							</a>
               	 	</div>
                <?php } ?>
                <?php if (count($promotion_bottom_banners)<5) {?>
                	<?php for($j=1; $j<=4-count($promotion_bottom_banners);$j++){?>
          	  			<a href="<?php echo Utilities::generateUrl('user','advertise')?>"><div class="grid-item">
                        		<div class="blank-text"><?php echo Utilities::getLabel('L_Space_available_for_advertising') ?></div>
		               	</div></a>
	            	<?php }?>
                <?php } ?>
                </div></div>
        <?php } else {?>
       	 	<?php if (!empty($home_page_elements["banners"])) {?>
        	<div class="section">
          		<div class="adds">
          		<?php foreach($home_page_elements["banners"] as $sn=>$val):?>
          	  	<div class="grid-item">
	           	   	<?php if ($val["banner_type"]==0){?>
	                    <?php if (!empty($val["banner_url"])) { ?>
			        		<a href="<?php echo $val["banner_url"]?>" target="<?php echo ($val["banner_link_newtab"]==1)? '_blank':'' ?>">
        	   		 	<?php } ?>
	                    <img class="load_later  img-responsive" src="<?php echo CONF_WEBROOT_URL?>images/1px.png" data-img-src="<?php echo Utilities::generateUrl('image','banner',array($val["banner_image_path"],$sn==0?'LARGE':'MEDIUM'))?>" alt="<?php echo $val["banner_title"]?>" />
    	                <?php if (!empty($val["banner_url"])) { ?></a><?php } ?>
                    <?php } else { echo ($val["banner_html"]); }?>
               	</div>
            	<?php endforeach;?>
          		</div>
        	</div>
        	<?php } ?>
        <?php } ?>
       
      </div>
    </div>	    
</div>
