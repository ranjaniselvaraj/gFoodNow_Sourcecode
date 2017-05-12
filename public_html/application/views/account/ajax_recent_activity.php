<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>								 
								 <?php if (count($arr_listing)>0) {
									$cnt=0; foreach ($arr_listing as $sn=>$row): $sn++;  
									switch($row["uact_action_performed"]){
										case "FV":
											$action_performed=Utilities::getLabel('L_favorited');
										break;
										case "P":
											$action_performed=Utilities::getLabel('L_Purchased');
										break;
											
									} ?>
           							<li class="user_activity">
                                		<div class="groupbox">
	                                        <?php if ($row["type"]==1): $product_id=$row["uact_prod_shop_record"]; ?>
                                        	<div class="thumb_desc">
                                            	<div class="photo"><img alt="" src="<?php echo Utilities::generateUrl('image','user',array($row['activity_from_profile_image'],'MEDIUM'))?>"></div>
                                                <span class="thumb_info"><strong><?php echo ($row['uact_from_user']!=$loggedin_user['user_id'])?$row["activity_from_name"]:Utilities::getLabel('L_You')?></strong> <?php echo $action_performed?> <?php echo Utilities::getLabel('L_this')?> <a href="<?php echo Utilities::generateUrl('products','view',array($product_id))?>"><?php echo Utilities::getLabel('L_Item')?></a>. </span>
                                            </div>
                                            <div class="thumbSingle">
                                            	<a href="<?php echo Utilities::generateUrl('products','view',array($product_id))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($product_id,'LARGE'))?>" alt="<?php echo $row["prod_name"]?>"></a>
                                            </div>
                                            <div class="bottom">
                                                <span class="itemname"><a href="<?php echo Utilities::generateUrl('products','view',array($product_id))?>"><?php echo $row["prod_name"];?></a></span>
                                                <span class="namestore"><a href="<?php echo Utilities::generateUrl('shops','view',array($row["shop_id"]))?>"><?php echo $row["prod_shop_name"];?></a></span>
                                                <span class="itemprice"><?php echo $buying_price?></span>
                                            </div>
                                           <?php elseif ($row["type"]==2):?>
                                        	<div class="thumb_desc">
                                            	<div class="photo"><img alt="" src="<?php echo Utilities::generateUrl('image','user',array($row['activity_from_profile_image'],'MEDIUM'))?>"></div>
                                                <span class="thumb_info"><strong><?php echo ($row['uact_from_user']!=$loggedin_user['user_id'])?$row["activity_from_name"]:Utilities::getLabel('L_You')?></strong> <?php echo $action_performed?> <?php echo Utilities::getLabel('L_this')?> <a href="<?php echo Utilities::generateUrl('shops','view',array($row["uact_prod_shop_record"]))?>"><?php echo Utilities::getLabel('L_Shop')?></a>. </span>
                                            </div>
                                            <div class="thumbsfour">
                                            		
                                            	<?php foreach($row["products"] as $skey=>$sval):?>
                                            	<a href="<?php echo Utilities::generateUrl('products','view',array($sval["prod_id"]))?>">
                                                    <figure class="thumbsquare"><img src="<?php echo Utilities::generateUrl('image','product_image',array($sval["prod_id"],'THUMB'))?>" alt=""></figure></a>
                                                 <?php endforeach;?>   
                                            </div>
                                            <div class="bottom">
                                                <span class="collectiontitle"><a href="<?php echo Utilities::generateUrl('shops','view',array($row["uact_prod_shop_record"]))?>"><?php echo $row["prod_name"]?></a></span>
                                                <span class="txtcount"><?php echo $row["shop_total_items"]?> <?php echo Utilities::getLabel('M_items')?></span>
                                            </div>
                                           <?php endif;?>
                                        </div>
                                	</li>
								<?php endforeach; } else {?>
	                                 <div class="alert alert-info">
							            <?php echo Utilities::getLabel('L_You_do_not_have_activities')?>
						            </div>
                                <?php } ?>
