<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      <div class="profile-head clearfix">
        <div class="fixed-container">
          <div class="fl">
            <div class="about-me">
              <div class="avatar"><img src="<?php echo Utilities::generateUrl('image','user',array($user_details["user_profile_image"],'SMALL'))?>" alt="<?php echo $user_details["user_username"]?>"/></div>
              <div class="name"><?php echo $user_details["user_username"]?> <span><?php echo $user_details["state_name"]?>, <?php echo $user_details["country_name"]?></span></div>
            </div>
          </div>
          <div class="fr">
            <div class="items-list">
              <ul>
                <li><a href="<?php echo Utilities::generateUrl('custom','favorite_items',array($user_details["user_id"]))?>"><span><?php echo $user_details["favItems"]?></span><?php echo Utilities::getLabel('L_Favorite_Items')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('custom','favorite_shops',array($user_details["user_id"]))?>"><span><?php echo $user_details["favShops"]?></span><?php echo Utilities::getLabel('L_Favorite_Shops')?></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="fixed-container">
		<div class="shop-page">
          <div class="shop-list clearfix">
          
          	<?php $cnt=0; foreach ($favourite_shops as $sn=>$row): $sn++; ?>
            <div class="shop-item">
              <div class="top">
                <div class="avatar-container">
                  <div class="avatar"><img src="<?php echo Utilities::generateUrl('image','shop_logo',array($row["shop_logo"],'THUMB'))?>" alt="<?php echo $row["shop_name"]?>"></div>
                  <div class="name"> <?php echo $row["shop_name"]?> <span><?php echo $row["state_name"]?>, <?php echo $row["country_name"]?> </span></div>
                  
                  <?php if ($row["totReviews"]>0):?>
                  <div class="reviewlist">
                    <ul class="rating">
                      <?php for($j=1;$j<=5;$j++){ ?>	
                  		<li class="<?php echo $j<=round($row["shop_rating"])?"active":"in-active" ?>"><svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                        <g><path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" fill="<?php echo $j<=round($row["shop_rating"])?"#ff3a59":"#474747" ?>" /></g>
                    	</svg></li>
                  		<?php } ?>  
                    </ul>
                    <p><?php echo $row["totReviews"]?> <?php echo Utilities::getLabel('L_Reviews')?></p>
                  </div>
                  <?php endif;?>
                </div>
              </div>
              <div class="item-body">
                
                <?php $inc=0; foreach ($row["products"] as $key=>$val): $inc++; if ($inc<=1): ?>
                <div class="image"><a href="<?php echo Utilities::generateUrl('products','view',array($val["prod_id"]))?>"><img class="img-responsive" src="<?php echo Utilities::generateUrl('image','product',array('MEDIUM',$val["image_file"]))?>" alt="<?php echo $val["prod_name"]?>"></a></div>
                <div class="caption"> <span class="name product-name-red"> <a href="<?php echo Utilities::generateUrl('products','view',array($val["prod_id"]))?>"><?php echo subStringByWords($val["prod_name"],66)?> </a></span> <span class="brand-name"><a href="<?php echo Utilities::generateUrl('shops','view',array($val["prod_shop"]))?>"><?php echo $val["shop_name"];?></a></span> </div>
                <?php if ($val['prod_sale_price']) { ?>
                <div class="price"> 
                	<?php if (!$val['special']) { ?>
	                    <span class="price-new"><?php echo Utilities::displayMoneyFormat($val['prod_sale_price'],true,true)?></span> 
                    <?php } else{ ?>
                  		<span class="price-new"><?php echo Utilities::displayMoneyFormat($val['special'],true,true); ?></span> <span class="price-old"><?php echo Utilities::displayMoneyFormat($val['prod_sale_price'],true,true); ?></span>
                  <?php } ?> 
                      </div>
                <?php } ?>
                <?php endif; endforeach; ?>
                
                <div class="overlay"><a id="shop_<?php echo $row["shop_id"]?>" class="favourite favShop <?php if($row["favorite"]):?>active<?php endif;?>" href="javascript:void(0)"></a> </div>
              </div>
              <div class="bttm">
                <ul class="smallthumbs">
                  <?php $inc=0; foreach ($row["products"] as $key=>$val): $inc++; if ($inc>1 && $inc<5): ?>
                  	<li><a href="<?php echo Utilities::generateUrl('products','view',array($val["prod_id"]))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($val['prod_id'],'THUMB'))?>" alt="<?php echo $val["prod_name"];?>"></a></li>
                  <?php endif; endforeach; ?>
                  <li><a class="boxblue" href="<?php echo Utilities::generateUrl('shops','view',array($val["prod_shop"]))?>"><?php echo $row["totStoreProducts"]?> <span><?php echo Utilities::getLabel('M_items')?></span></a></li>
                </ul>
              </div>
            </div>
			<?php endforeach;?>
            
          </div>
        </div>
        
        
      </div>
    </div>
  </div>
  
