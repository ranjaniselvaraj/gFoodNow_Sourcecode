<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php  global $prod_condition;?>
<div>
    <div class="body clearfix">
      <div class="breadcrumb">
        <div class="fixed-container">
          <ul>
            <li><a href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Home') ?></a></li>
            <?php foreach($product_info["product_category"] as $pcat){?>
            	<li><a href="<?php echo Utilities::generateUrl('category','view',array($pcat["category_id"]))?>"><?php echo $pcat["category_name"]?></a></li>	
            <?php };?>
            <li><?php echo $product_info["prod_name"]?></li>
          </ul>
        </div>
      </div>
      <div itemscope itemtype="http://schema.org/Product">
      <div class="fixed-container">
        <div class="product-detail clearfix">
          <h1 class="name" id="product_name"><span itemprop="name"><?php echo $product_info["prod_name"]?></span></h1> 
          
          <div class="left-side">
              <div class="gallery-side">
	            <?php  if (count($product_info["images"])):?>	
                <div id="product-gallery" class="eagle-gallery img400">
                  <div class="owl-carousel"> 
                  	<?php foreach ($product_info["images"] as $iKey=>$iVal): ?>
                  	<img src="<?php echo Utilities::generateUrl('image','product',array('THUMB',$iVal["image_file"]))?>" data-medium-img="<?php echo Utilities::generateUrl('image','product',array('LARGE',$iVal["image_file"]))?>" data-big-img="<?php echo Utilities::generateUrl('image','product',array('BIG',$iVal["image_file"]))?>" data-title="<?php echo $product_info["prod_name"]?>" alt="<?php echo $product_info["prod_name"]?>"> 
                    <? endforeach; ?>
                   </div>
                </div>
                <?php else:?>
                    <img src="<?php echo Utilities::generateUrl('image','product',array('LARGE',$iVal["image_file"]))?>" alt="<?php echo $product_info["prod_name"]?>">
                   <?php endif;?>
              </div>
            </div>
          
          
          
          <!--gallery-->
          
          
          
          
          
          
          
          
          <div class="pro-description">
          		  
            <div class="in-border">
               
              <div class="upper clearfix">
              	<? if ($product_info['prod_stock']<1):?>
               <div class="out-stock-text">
		                <span class="txt"><?php echo Utilities::getLabel('M_Out_of_Stock')?></span>
	           </div>
              <?php endif;?>
              	<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                <meta itemprop="priceCurrency" content="<?php echo Settings::getSetting("CONF_CURRENCY");?>" />
              	<?php  $price=$product_info['prod_sale_price'];?>
              	<?php if ($product_info['special']) { ?>
                <div class="price-side"><?php echo Utilities::getLabel('L_Original_Price') ?>: 
                <span class="striked" ><?php echo Utilities::displayMoneyFormat($price,true,true); ?></span> 
                <span class="actuall-price" itemprop="price" ><?php echo Utilities::displayMoneyFormat($product_info['special'],true,true); ?> </span> 
                	<small>
                		<? echo ($product_info['prod_type']==1)?Utilities::getLabel('L_Excluding_Shipping_Charges'):Utilities::getLabel('L_Not_eligible_for_shipping'); ?>
                   </small>  
                </div>
                <?php } else {?>
                	<div class="price-side"><span class="actuall-price" itemprop="price" ><?php echo Utilities::displayMoneyFormat($price,true,true); ?></span> 
                	<small>
                		<? echo ($product_info['prod_type']==1)?Utilities::getLabel('L_Excluding_Shipping_Charges'):Utilities::getLabel('L_Not_eligible_for_shipping'); ?>
                   </small>
                  </div>
                <?php  }?>
                </span>
                
                
                <div class="review-side">
	              <?php  if (($product_info["totReviews"]>0) && (Settings::getSetting("CONF_ALLOW_REVIEWS"))):?>
                  <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"> 
                  <span itemprop="ratingValue" class="hide"><?php echo round($product_info["prod_rating"])?></span>
                  <div class="review-star">
                    <ul class="rating">
                      <?php for($j=1;$j<=5;$j++){ ?>	
                 	  <li class="<?php echo $j<=round($product_info["prod_rating"])?"active":"in-active" ?>">
                    	<svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                    	<g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($product_info["prod_rating"])?"#ff3a59":"#474747" ?>" /></g></svg>
                        
                      </li>
                       <?php } ?>  
                      <li><span itemprop="reviewCount"><?php echo $product_info["totReviews"]?></span> <?php echo Utilities::getLabel('L_Reviews') ?></li>
                    </ul>
                  </div>
                  </span>
                  <?php  endif; ?>
                  <div class="review-write"> 
                      
                      <a class="logged_in" href="<?php echo Utilities::generateUrl('products', 'send_message',array($product_info["prod_id"]))?>">
                      <svg class="write" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="18px" height="18px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                        <path  d="M68.3,16.4L53.6,1.7C52.5,0.6,51,0,49.6,0c-1.5,0-2.9,0.6-4,1.7L4,43.2c-1.1,1.1-2.1,2.8-2.8,4.5
	C0.5,49.4,0,51.3,0,52.9V70h17.1c1.6,0,3.4-0.5,5.2-1.2c1.8-0.7,3.4-1.7,4.5-2.8l41.5-41.5c1.1-1.1,1.7-2.6,1.7-4
	C70,18.9,69.4,17.5,68.3,16.4z M10.5,47.4L42,16l4.7,4.7L15.2,52.1L10.5,47.4z M17.1,62.4h-5.7l-3.8-3.8v-5.7c0-0.3,0.1-1.2,0.6-2.3
	c0-0.1,11.3,11.1,11.3,11.1C18.2,62.2,17.4,62.4,17.1,62.4z M22.5,59.5l-4.7-4.7l31.5-31.5L54,28L22.5,59.5z M56.7,25.3l-12-12
	l4.9-4.9l12,12L56.7,25.3z"/>
                      </svg>
                      <?php echo Utilities::getLabel('L_Ask_a_Question') ?></a> <a class="<?php if ($product_info["favorite"]): echo 'active'; endif; ?> itemfav" id="item_<?php echo $product_info["prod_id"];?>" href="#">
                      <svg class="move" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="18px" height="18px" viewBox="0 0 70 70" enable-background="new 0 0 70 70" xml:space="preserve">
                        <g>
                          <g>
                            <path  d="M68.6,18.8L51.2,1.3c-0.9-0.9-2-1.3-3.1-1.3C47,0,45.9,0.4,45,1.3c-0.5,0.5-0.8,1-1,1.5
			c-3.6,7.6-7.6,11.9-12.7,14.4c-5.7,2.8-12.2,4.7-22.6,4.7c-0.6,0-1.1,0.1-1.7,0.3c-1.1,0.4-1.9,1.3-2.4,2.4
			c-0.4,1.1-0.4,2.3,0,3.3c0.2,0.5,0.5,1,0.9,1.4l14.2,14.2L0,69.9l26.4-19.8l14.2,14.2c0.4,0.4,0.9,0.7,1.4,0.9
			c0.5,0.2,1.1,0.3,1.7,0.3s1.1-0.1,1.7-0.3c1.1-0.4,1.9-1.3,2.4-2.4c0.2-0.5,0.3-1.1,0.3-1.7c0-10.5,1.9-17,4.7-22.6
			c2.5-5,6.8-9,14.4-12.7c0.6-0.2,1.1-0.5,1.5-1C70.4,23.3,70.4,20.5,68.6,18.8z M44.9,34.8c-2.6,5.2-4.2,10.5-5,16.5L32.6,44
			L26,37.4L18.7,30c6-0.8,11.4-2.5,16.6-5c5.6-2.8,10.1-7.1,13.9-13.4l9.1,9.2C52.1,24.6,47.7,29.1,44.9,34.8z"/>
                          </g>
                        </g>
                      </svg>
                      <?php echo Utilities::getLabel('L_Mark_Favorite')?></a> <a href="<?php echo Utilities::generateUrl('common', 'view_lists',array($product_info["prod_id"])); ?>" id="product_<?php echo $product_info["prod_id"]?>" class="listView">
                      <svg version="1.1" class="svg-icn"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 384.97 384.97" style="enable-background:new 0 0 384.97 384.97;" xml:space="preserve">
                        <g>
                          <g id="List">
                            <path d="M144.123,84.092l228.576,0.241c6.641,0,12.03-5.39,12.03-12.03c0-6.641-5.39-12.03-12.03-12.03l-228.576-0.241
			c-6.641,0-12.03,5.39-12.03,12.03C132.093,78.702,137.482,84.092,144.123,84.092z"/>
                            <path d="M372.939,180.455l-228.576-0.241c-6.641,0-12.03,5.39-12.03,12.03c0,6.641,5.39,12.03,12.03,12.03l228.576,0.241
			c6.641,0,12.03-5.39,12.03-12.03S379.58,180.455,372.939,180.455z"/>
                            <path d="M372.939,300.758l-228.576-0.241c-6.641,0-12.03,5.39-12.03,12.03s5.39,12.03,12.03,12.03l228.576,0.241
			c6.641,0,12.03-5.39,12.03-12.03C384.97,306.147,379.58,300.758,372.939,300.758z"/>
                            <path d="M48.001,24.301C21.486,24.301,0,45.787,0,72.302s21.486,48.001,48.001,48.001s48.001-21.486,48.001-48.001
			S74.516,24.301,48.001,24.301z M47.881,96.363c-13.522,0-24.361-10.539-24.361-24.061s10.839-24.061,24.361-24.061
			S71.941,58.78,71.941,72.302S61.403,96.363,47.881,96.363z"/>
                            <path d="M48.001,144.604C21.486,144.604,0,166.09,0,192.605s21.486,48.001,48.001,48.001s48.001-21.486,48.001-48.001
			S74.516,144.604,48.001,144.604z M47.881,216.666c-13.522,0-24.361-10.539-24.361-24.061c0-13.522,10.839-24.061,24.361-24.061
			s24.061,10.539,24.061,24.061C71.941,206.127,61.403,216.666,47.881,216.666z"/>
                            <path d="M48.001,264.667C21.486,264.667,0,286.153,0,312.668s21.486,48.001,48.001,48.001s48.001-21.486,48.001-48.001
			S74.516,264.667,48.001,264.667z M47.881,336.728c-13.522,0-24.361-10.539-24.361-24.061s10.839-24.061,24.361-24.061
			s24.061,10.539,24.061,24.061S61.403,336.728,47.881,336.728z"/>
                          </g>
                        </g>
                      </svg>
                      <?php echo Utilities::getLabel('L_Add_to_List')?> </a>
                      <div class="product_lists listcontainer display-div<?php echo $product_info["prod_id"]?>" id="display-div<?php echo $product_info["prod_id"]?>" data-href="<?php echo Utilities::generateUrl('common', 'view_lists',array($product_info["prod_id"])); ?>"></div><br/>
                      <div class="social-list marginTop">
                        <div class="sharewrap"> 
                        	<span title="ShareThis" class='st_sharethis_large' displayText='ShareThis'></span>
                            <span title="Facebook" class='st_facebook_large' displayText='Facebook'></span>
                            <span title="Tweet" class='st_twitter_large' displayText='Tweet'></span>
                            <span title="Pinterest" class='st_pinterest_large' displayText='Pinterest'></span>
                            <span title="WhatsApp" class='st_whatsapp_large' displayText='WhatsApp'></span>
                            <span title="Email" class='st_email_large' displayText='Email'></span>
                         </div>
                      </div>
                    </div>
                    
                    
                    
                </div>
              </div>
              <div class="selecting-things clearfix">
              	
                <div class="options">
                  
                  <ul class="list-unstyled">
                  	<?php  if ($product_info["prod_brand"]) {?>
                    <li><?php echo Utilities::getLabel('L_Brand') ?>: 
					<?php  if ($product_info["brand_status"]) { ?>
                    	<a itemprop="brand" itemscope itemtype="http://schema.org/Brand" href="<?php echo Utilities::generateUrl('brands', 'view',array($product_info["prod_brand"]))?>"> 
						<?php  } echo '<span itemprop="name">'.$product_info["brand_name"].'</span>';  if ($product_info["brand_status"]) { ?></a><?php  } ?></li>
                    <?php } ?>
                    <?php  if ($product_info["prod_model"]) {?>
                    <li><?php echo Utilities::getLabel('L_Model') ?>: <?php echo $product_info["prod_model"]?></li>
                    <?php  }?>
                    <?php  if ($product_info["prod_sku"]) {?>
                    <li><?php echo Utilities::getLabel('L_Product_Code') ?>: <span itemprop="sku"><?php echo $product_info["prod_sku"]?></span></li>
                    <?php  } ?>
                    <?php  if (($product_info["prod_condition"]) && ($product_info['prod_type']==1)) {?>
                    <li><?php echo Utilities::getLabel('L_Condition') ?>: <?php echo $prod_condition[$product_info["prod_condition"]]?></li>
                    <?php  } ?>
                    <?php  if ($product_info["prod_ship_free"]) {?>
                    <li><?php echo Utilities::getLabel('L_Shipping_Free') ?>: <?php echo Utilities::getLabel('L_Yes') ?> </li>
                    <?php  } ?>
                    <?php  if ($product_info["prod_available_date"]) {?>
                    <li><?php echo Utilities::getLabel('L_Available_Date') ?>: <?php echo Utilities::formatDate($product_info["prod_available_date"])?> </li>
                    <?php  } ?>
                    <?php  if ($product_info["prod_length"] || $product_info["prod_width"] || $product_info["prod_height"]) {?>
                    <li><?php echo Utilities::getLabel('L_Dimensions') ?>: <?php echo $product_info["prod_length"]." x ". $product_info["prod_width"]." x ".$product_info["prod_height"]." ".$product_info["prod_length_class"]?>s</li>
                    <?php  } ?>
                    <?php  if ($product_info["prod_weight"]>0) {?>
                    <li><?php echo Utilities::getLabel('L_Weight') ?>: <?php echo $product_info["prod_weight"]." ".$product_info["prod_weight_class"]?>s</li>
                    <?php  } ?>
                    <?php if (Settings::getSetting("CONF_ENABLE_COD_PAYMENTS") && $product_info['prod_type']==1):?>  
                    <li><?php echo Utilities::getLabel('L_COD_cash_on_delivery') ?>: <strong><?php echo $product_info["cod_enabled"]?Utilities::getLabel('L_Available'):Utilities::getLabel('L_Not_Available') ?></strong></li>
                    <?php endif;?>
                    
                  </ul>
                  <?php if ($product_info["discounts"]) {?>
    	             <hr/>
                 	 <ul class="list-unstyled">
                  		<?php foreach ($product_info["discounts"] as $discount) { ?>	
	                        <li><span class='offer--tag'><?php echo Utilities::getLabel('L_Discount_Offers') ?></span><?php echo $discount["pdiscount_qty"]?> <?php echo Utilities::getLabel('L_or_more') ?> <?php echo Utilities::displayMoneyFormat($discount['pdiscount_price'],true,true); ?></li>
                        <?php  } ?>
	                  </ul>
	                  <br>
                  <?php } ?>
                  <br>
                 
                    <div id="product">
                    	<?php if (count($product_info["addon_products"])>0) { ?>	
                 		<div class="cart-box">
                 		  <div class="h4"><?php echo Utilities::getLabel('L_Product_Add-ons')?></div>	
                          <table class="cart-tbl">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th><?php echo Utilities::getLabel('L_Name')?></th>
                                    <th><?php echo Utilities::getLabel('L_Price')?></th>
                                    <th><?php echo Utilities::getLabel('L_QTY')?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            
                            <?php  foreach ($product_info["addon_products"] as $product) { ?>
                            <tr>
                              <td><div class="pro-image"><a href="<?php echo Utilities::generateUrl('products','view',array($product['prod_id']))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($product["prod_id"],'MINI'))?>" alt=""> </a></div></td>
                              <td><div class="product-name"><span class="mobile-caption"><?php echo Utilities::getLabel('L_Name')?>:</span><a href="<?php echo Utilities::generateUrl('products','view',array($product['prod_id']))?>" ><?php echo $product['prod_name']?></a></div></td>
                              <td><div class="price"><span class="mobile-caption"><?php echo Utilities::getLabel('L_Price')?>:</span><?php 
								  $price=$product['prod_sale_price'];
								  if (!$product['special']) { ?>
									<span><?php echo Utilities::displayMoneyFormat($price,true,true)?></span> 
									<?php } else{ ?>
									<span><?php echo Utilities::displayMoneyFormat($product['special'],true,true); ?></span> <span class="price-old"><?php echo Utilities::displayMoneyFormat($price,true,true); ?></span>
								  <?php } ?> </div></td>
                              <td><div class="qty">
                                  <input type="text" value="1" class="form-control addons" lang="addons[<?php echo $product['prod_id']?>]"  name="addons[<?php echo $product['prod_id']?>]">
                                </div></td>
                              <td><a href="javascript:void(0)" class="actions cancel"><img width="16" height="17" src="<?php echo CONF_WEBROOT_URL?>images/action-cross.png" alt=""></a></td>
                            </tr>
                            <tr><td colspan="5" class="noPadding"><span id="product-addon<?php echo $product['prod_id'];?>"></span></td></tr>
							<?php } ?>
                          </table>
                          
                        </div>
                        <div class="gap"></div>	
                        <?php } ?>	
                    	<?php if ($product_info["options"]) { ?>
	                    	<div class="heading"> <strong><?php echo Utilities::getLabel('L_Available_options') ?></strong> </div>
	                    <?php } ?>  
    	                <div class="form-group">
        		            <?php echo $product_form->getFormHtml(); ?>
                	    </div>
                        
                    </div>
                    
                    
                  
                </div>
              </div>
              <div class="cart-bottom">
                <ul>
                  <li><strong class="txt-15"><?php echo Utilities::getLabel('M_SELLER_INFORMATION') ?></strong><br>
                    <?php echo Utilities::getLabel('L_Sold_By') ?>: <strong  class="blue-txt"><a href="<?php echo Utilities::generateUrl('shops', 'view',array($product_info['prod_shop'])) ?>"><?php echo $product_info["shop"]["shop_name"]?></a> </strong> </li>
                   
                  <li> <!--?xml version="1.0" encoding="utf-8"?--> 
	                 <?php  if (Settings::getSetting("CONF_ALLOW_REVIEWS")):?> 
                   	 <ul class="rating">
                      <?php for($j=1;$j<=5;$j++){ ?>	
                  		<li class="<?php echo $j<=round($product_info["shop"]["shop_rating"])?"active":"in-active" ?>"><svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                        <g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($product_info["shop"]["shop_rating"])?"#ff3a59":"#474747" ?>" /></g>
                    	</svg></li>
                  		<?php } ?>  
                    </ul>
                    
                    <?php  endif; ?>
                    <br>
                    
                    <strong> <?php echo Utilities::getLabel('L_Location') ?>:</strong> <?php echo $product_info["shop"]["state_name"]?>, <?php echo $product_info["shop"]["country_name"]?></li>
                  <?php  if (Settings::getSetting("CONF_ALLOW_REVIEWS")):?> 
                  <li><strong class="txt-15"><?php echo $product_info["shop"]["totReviews"]?></strong><br>
                    <?php echo Utilities::getLabel('L_Reviews') ?></li>
                  <?php  endif; ?>  
                  <li><strong class="txt-15"><?php echo $product_info["shop"]["totProducts"]?></strong><br>
                    <?php echo Utilities::getLabel('L_Products') ?> </li>
                </ul>
              </div>
            </div>
          </div> 
        </div>
        
                  
        
        <div class="gap"></div>
        <?php echo $frmSearch->getFormHtml();?>
        
        <div class="clear"></div>
			
        <div class="description-tabs">
            <ul class="tabs">
               <li><a href="#tab1" class="ripple"><?php echo Utilities::getLabel('L_Description') ?></a></li>
              <?php if ($product_info["attribute_groups"]) { ?><li><a href="#tab4" class="ripple"><?php echo Utilities::getLabel('L_Specifications')?></a></li><? } ?>
			  <?php if (($product_info["totReviews"]>0) && Settings::getSetting("CONF_ALLOW_REVIEWS")) { ?><li><a href="#tab2" class="ripple"><?php echo Utilities::getLabel('L_Reviews') ?></a></li>
              <? } ?>
              <li><a href="#tab3" class="ripple"><?php echo Utilities::getLabel('L_Shipping_Policies')?></a></li>
              
            </ul>
          
			<div class="tab_content desc-txt" id="tab1">
                  <?php 
				  $youtube_embed_code=Utilities::parse_yturl($product_info["prod_youtube_video"]);
							if($youtube_embed_code!=""):?>
						<div class="videowrap">
							<iframe width="60%" height="300" src="//www.youtube.com/embed/<?php echo $youtube_embed_code?>" frameborder="0" allowfullscreen></iframe>
						</div>
						<span class="gap"></span>
					<?php  endif;?>    
	    		    <span itemprop="description"><?php echo Utilities::renderHtml($product_info["prod_long_desc"],true)?></span>
                  
              </div>
             <?php if ($product_info["attribute_groups"]) { ?>
              <div class="tab_content desc-txt" id="tab4">
                  <table  class="table table-borded table-spec">
                  <tbody >
                  <?php foreach ($product_info["attribute_groups"] as $attribute_group) { ?>	
                    <tr >
                      <th><h4><?php echo $attribute_group["name"];?></h4></th>
                      <td > <?php foreach ($attribute_group["attribute"] as $attribute) { ?><div  class="row">
                          <div  class="col-xs-5">
                            <h5 ><strong ><?php echo $attribute["name"]?></strong></h5>
                          </div>
                          <div  class="col-xs-7"><?php echo $attribute["text"]?></div>
                        </div>
                        <?php }?>
						</td>
                        
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
              <? } ?>
			  <?php if (($product_info["totReviews"]>0) && Settings::getSetting("CONF_ALLOW_REVIEWS")) { ?>
              <div class="tab_content" id="tab2">
                <div id="customer-reviews">
                   <?php if (($product_info["totReviews"]>0) && Settings::getSetting("CONF_ALLOW_REVIEWS")) { ?>
       				 <div class="review">
			          <h4><?php echo Utilities::getLabel('L_Reviews_of') ?> <?php echo $product_info["prod_name"]?></h4>
			          <section class="allreviews">
	        		      <span id="reviews-list"></span>
			          </section>
       				</div>
    		       <?php } ?>
                </div>
              </div>
              <?php } ?>
              
           
			 <div class="tab_content desc-txt" id="tab3">
                <?php  if (!empty($product_info["shipping_rates"]) && ($product_info["prod_ship_free"]==0)):?>
                <table class="specTable">
	    	            <tr>
    	    	    	    <th class="groupHead"><?php echo Utilities::getLabel('L_Ship_to')?></th>
                			<th class="groupHead"><?php echo Utilities::getLabel('L_Cost')?></th>
			                <th class="groupHead"><?php echo Utilities::getLabel('L_With_another_item')?></th>
        	    	    </tr>
		                <?php  foreach($product_info["shipping_rates"] as $key=>$val):?>
    	        	    <tr>
        	        		<td><em><strong><?php echo $val["country_name"]!=""?$val["country_name"]:Utilities::getLabel('L_Everywhere_else')?></strong></em> <?php echo Utilities::getLabel('L_by')?> <strong><?php echo $val["scompany_name"]?></strong> <?php echo Utilities::getLabel('L_in')?> <?php echo $val["sduration_label"]?></td>
	        	        	<td><?php echo Utilities::displayMoneyFormat($val["pship_charges"])?></td>
	    	    	        <td><?php echo Utilities::displayMoneyFormat($val["pship_additional_charges"])?></td>
    	            	</tr>
	    	            <?php  endforeach;?>
	                </table>
                <? elseif ($product_info["prod_ship_free"]==1):?>    
                	<p><?php echo Utilities::getLabel('L_Ship_Free_Product')?></p>
                <?php  endif;?>
                
                <div class="gap"></div>
                <h4><?php echo sprintf(Utilities::getLabel('L_Shop_Policies'),$product_info["shop"]["shop_name"])?></h4>
                <div class="cmsContainer colscontainer">
                <?php  if ($product_info["shop"]["shop_payment_policy"]!=""):?>
                    <div class="rowcontent">
                    <h5><?php echo Utilities::getLabel('L_Payment')?></h5>
                    <p><?php echo Utilities::renderHtml($product_info["shop"]["shop_payment_policy"])?></p>
                    </div>
                <?php  endif;?>
                <?php  if ($product_info["shop"]["shop_delivery_policy"]!=""):?>
                    <div class="rowcontent">
                    <h5><?php echo Utilities::getLabel('L_Shipping')?></h5>
                    <p><?php echo Utilities::renderHtml($product_info["shop"]["shop_delivery_policy"])?></p>
                    </div>
                <?php  endif;?>
                <?php  if ($product_info["shop"]["shop_refund_policy"]!=""):?>
                    <div class="rowcontent">
                    <h5><?php echo Utilities::getLabel('L_Refunds_Exchanges')?></h5>
                    <p><?php echo Utilities::renderHtml($product_info["shop"]["shop_refund_policy"])?></p>
                    </div>
                <?php  endif;?>
                <?php  if ($product_info["shop"]["shop_additional_info"]!=""):?>
                    <div class="rowcontent">
                    <h5><?php echo Utilities::getLabel('L_Additional_Policies_FAQs')?></h5>
                    <p><?php echo Utilities::renderHtml($product_info["shop"]["shop_additional_info"])?></p>
                    </div>
                <?php  endif;?>
                <?php  if ($product_info["shop"]["shop_seller_info"]!=""):?>
                    <div class="rowcontent">
                    <h5><?php echo Utilities::getLabel('L_Seller_Information')?></h5>
                    <p><?php echo Utilities::renderHtml($product_info["shop"]["shop_seller_info"])?></p>
                    </div>
                <?php  endif;?>
                <div class="rowcontent">
	                <span class="smallItalicText"><?php echo Utilities::getLabel('L_Last_updated')?> <?php echo Utilities::formatdate($product_info["shop"]["shop_update_date"])?></span>
                </div>
                </div>
                 
              </div>
              
          </div>
        
        <div class="gap"></div>
        <?php if ($product_info["related"]) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Related_Products') ?></h3>
    	        </div>
                <div class="shop-list products-carousel <? if (count($product_info["related"])<6):?>less_items<?php endif; ?>">
		            <?php  foreach ($product_info["related"] as $product) {  
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
        </div>
        <?php } ?>
        <div class="gap"></div>
        
        <?php if ($product_info["smart_recommended_products"]) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Recommended_Products_For_You') ?></h3>
    	        </div>
                <div class="shop-list products-carousel <? if (count($product_info["smart_recommended_products"])<6):?>less_items<?php endif; ?>">
		            <?php  foreach ($product_info["smart_recommended_products"] as $product) {  
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
            </div>
        <?php } ?>
        <div class="gap"></div>
        
        <?php if ($product_info["also_bought_products"]) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Customers_who_bought_this_also_bought') ?></h3>
    	        </div>
                <div class="shop-list products-carousel <? if (count($product_info["also_bought_products"])<6):?>less_items<?php endif; ?>">
		            <?php  foreach ($product_info["also_bought_products"] as $product) {  
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
		            }  ?>
        	    </div>
            </div>
        <?php } ?>
        
        
      </div>
      </div>
      
    </div>
  </div>
<!-- End Here is the Twitter Card code for this product  --> 
          	
  
  <!--wrapper end here-->
<script type="text/javascript">var switchTo5x=true;</script>
<!--<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>-->
<?php if (Settings::getSetting("CONF_USE_SSL")==1) {?>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<?php }else{?>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<?php }?>
<script type="text/javascript">stLight.options({publisher: "c1ac1329-15e5-4379-aa5c-30b5671f7265", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>		