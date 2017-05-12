<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo $user_details["user_name"]?>'s <?php echo Utilities::getLabel('L_Favorites')?></h3>
          <ul class="arrowTabs">
            <li><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"><?php echo Utilities::getLabel('M_Items')?></a></li>
            <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'favorite_shops')?>"><?php echo Utilities::getLabel('L_Shops')?></a></li>
          </ul>
          <?php if (count($favourite_shops)>0) {?>
          <div class="allrows">
                        	<?php foreach($favourite_shops as $favkey=>$favval):?>	
                        	<section class="repeatedRow clearfix">
                            	<aside class="grid_1">
                                	<div class="grid_3">
                                		<span class="nameshop"><a href="<?php echo Utilities::generateUrl('shops','view',array($favval["shop_id"]))?>"><?php echo $favval["shop_name"]?></a></span>
                                    </div>
                                    <div class="grid_4">
                                        <div class="ownerinfo">
                                            <img class="photo" alt="" src="<?php echo Utilities::generateUrl('image', 'shop_logo', array($favval['shop_logo'],'thumb'))?>"><h4><span><?php echo Utilities::getLabel('L_Shop_Owner')?></span><?php echo $favval["user_name"]?></h4>
                                        </div>
                                    </div>
                                    <a class="removelink buttonfavshop" id="shop_<?php echo $favval["shop_id"]?>" href="javascript:void(0)"></a>
                                </aside>
                                <aside class="grid_2">
                                	<ul class="squareBoxes">
                                    	<?php foreach($favval["products"] as $pkey=>$pval): if ($pkey<4):?>
                                    	<li><a href="<?php echo Utilities::generateUrl('products','view',array($pval["prod_id"]))?>"><div class="box_square"><img alt="" src="<?php echo Utilities::generateUrl('image','product_image',array($pval['prod_id'],'THUMB'))?>"></div></a></li>
                                        <?php endif; endforeach;?>
                                        
                                        <li><a href="<?php echo Utilities::generateUrl('shops','view',array($favval["shop_id"]))?>" class="boxblue"><?php echo $favval["totStoreProducts"]?> <span><?php echo Utilities::getLabel('M_items')?></span></a></li>
                                    </ul>
                                </aside>
                            </section>
                            <?php endforeach;?>
                   </div>
               <?php } else {?>
               	   <div class="space-lft-right">	    
		               <div class="alert alert-info">
        		        <?php echo Utilities::getLabel('L_You_do_not_have_any_favorite_shop')?>
		          		</div>
                    </div>    
               <?php } ?>
        </div>
        <!--right end--> 
        
      </div>
    </div>
  </div>
