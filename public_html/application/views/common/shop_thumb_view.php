<div class="shop-item equal_height_shop_item <? if ($row["promotion_id"]>0):?>thumb_impression thumb_click<?php endif;?> " data-attr-id="<?php echo $row["promotion_id"]?>" >
			  
			  		
              <div class="top">
                <div class="shop_header">
              	<? if ($row["promotion_id"]>0):?>	
              	<div class="lable"><?php echo Utilities::getLabel('M_Sponsored')?></div>
              	<?php endif;?>
                <div class="avatar-container">
	                  <div class="avatar">
		                      <a  class="ppc_promotion_click" ref="<?php echo Utilities::generateUrl('shops','view',array($row["shop_id"]))?>">	
				                  <img src="<?php echo Utilities::generateUrl('image','shop_logo',array($row["shop_logo"],'THUMB'))?>" alt="<?php echo $row["shop_name"]?>">
                              </a>
        	          </div>
				  <div class="name_reviewList">
                  	<div class="name"> <a class="ppc_promotion_click" href="<?php echo Utilities::generateUrl('shops','view',array($row["shop_id"]))?>"><?php echo $row["shop_name"]?></a> <span><?php echo $row["state_name"]?>, <?php echo $row["country_name"]?> </span></div>
	                  <?php  if (Settings::getSetting("CONF_ALLOW_REVIEWS")):?>
    	              <div class="reviewlist">
        	            <ul class="rating">
            	         <?php for($j=1;$j<=5;$j++){ ?>	
                  		<li class="<?php echo $j<=round($row["shop_rating"])?"active":"in-active" ?>" ><svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                        <g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($row["shop_rating"])?"#ff3a59":"#474747" ?>" /></g>
                    	</svg></li>
                  		<?php } ?>  
                    </ul>
                    <p><?php echo $row["totReviews"]?> <?php echo Utilities::getLabel('L_Reviews')?></p>
                  </div>
                  <?php  endif;?>
                  </div>
                </div>
              </div>
              </div>
              <div class="item-body">
                
                <?php  $inc=0; foreach ($row["products"] as $key=>$val): $inc++; if ($inc<=1): ?>
                <div class="image"><a href="<?php echo Utilities::generateUrl('products','view',array($val["prod_id"]))?>"><img class="img-responsive" src="<?php echo Utilities::generateUrl('image','product_image',array($val["prod_id"],'MEDIUM'))?>" alt="<?php echo $val["prod_name"];?>"></a></div>
                <div class="caption"> <span class="name product-name-red"> <a href="<?php echo Utilities::generateUrl('products','view',array($val["prod_id"]))?>"><?php echo subStringByWords($val["prod_name"],66)?> </a></span> <span class="brand-name"><a class="ppc_promotion_click" href="<?php echo Utilities::generateUrl('shops','view',array($val["prod_shop"]))?>"><?php echo $val["shop_name"];?></a></span> </div>
                <?php if ($val['prod_sale_price']) { ?>
                <div class="price"> 
                	<?php if (!$val['special']) { ?>
	                    <span class="price-new"><?php echo Utilities::displayMoneyFormat($val['prod_sale_price'],true,true)?></span> 
                    <?php } else{ ?>
                  		<span class="price-new"><?php echo Utilities::displayMoneyFormat($val['special'],true,true); ?></span> <span class="price-old"><?php echo Utilities::displayMoneyFormat($val['prod_sale_price'],true,true); ?></span>
                  <?php } ?> 
                      </div>
                <?php } ?>
                <?php  endif; endforeach; ?>
                
                <div class="overlay"><a id="shop_<?php echo $row["shop_id"]?>" class="favourite favShop <?php  if($row["favorite"]):?>active<?php  endif;?>" href="javascript:void(0)"></a> </div>
              </div>
              <div class="bttm">
                <ul class="smallthumbs">
                  <?php  $inc=0; foreach ($row["products"] as $key=>$val): $inc++; if ($inc>1 && $inc<5): ?>
                  	<li><a href="<?php echo Utilities::generateUrl('products','view',array($val["prod_id"]))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($val['prod_id'],'THUMB'))?>" alt="<?php echo $val["prod_name"];?>"></a></li>
                  <?php  endif; endforeach; ?>
                  <li><a class="boxblue ppc_promotion_click"  href="<?php echo Utilities::generateUrl('shops','view',array($val["prod_shop"]))?>"><?php echo $row["totProducts"]?> <span><?php echo Utilities::getLabel('M_items')?></span></a></li>
                </ul>
              </div>
            </div>