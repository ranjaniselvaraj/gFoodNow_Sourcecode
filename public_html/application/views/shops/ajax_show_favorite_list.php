<?php  foreach($favorite_users as $key=>$val):?>
    <section class="repeatedRow clearfix">
        <aside class="grid_1">
            <div class="f_info">
            <img src="<?php echo Utilities::generateUrl('image','user',array($val['userfav_profile_image'],'SMALL'))?>" alt="<?php echo $val["userfav_username"]?>" class="photo">
            <h4><?php echo $val["userfav_username"]?></h4>
            <p class="txtNorml"><?php echo Utilities::getLabel('L_Favorite_Shop')?>: <span><a href="<?php echo Utilities::generateUrl('custom','favorite_shops',array($val["userfav_id"]))?>"><?php echo $val["favShops"]?></a></span> </p>
            </div>
         </aside>
	     <aside class="grid_2">
    		<ul class="squareBoxes">
		    <?php  foreach($val["products"] as $skey=>$sval):?>
			    <li><div class="box_square"><a href="<?php echo Utilities::generateUrl('products','view',array($sval["prod_id"]))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($sval['prod_id'],'THUMB'))?>" alt="<?php echo $sval['prod_name']?>"></a></div></li>
		    <?php  endforeach;?>
		    <li><a href="<?php echo Utilities::generateUrl('custom','favorite_items',array($val["userfav_id"]))?>" class="boxblue"><?php echo $val["total_records"]?> <span><?php echo Utilities::getLabel('L_Favorites')?></span></a></li>
		    </ul>
	    </aside>
    </section>
<?php  endforeach;?>
<div class="clear"></div>
<?php  if ($page<$pages):?>
<div class="loadmorepage">
		<div class="aligncenter">
		   <a href="javascript:void(0)" onclick="listPagingFavorites('<?php echo $page+1?>');" class="loadmore btn"><?php echo Utilities::getLabel('F_Load_More')?></a>
		</div>
 </div>
<?php  endif;?>
