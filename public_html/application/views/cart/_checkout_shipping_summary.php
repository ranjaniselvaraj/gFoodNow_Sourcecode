<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<form action="<?php echo Utilities::generateUrl('cart','save_shipping_address')?>" method="post" enctype="multipart/form-data" id="frmSaveShipping">	
              <table width="100%" cellspacing="0" cellpadding="0" border="0" class="cart-tbl">
                <thead>
                  <tr>
                    <th width="15%"><?php echo Utilities::getLabel('M_Product')?> </th>
                    <th width="35%"><?php echo Utilities::getLabel('M_Product_Information')?> </th>
                    <th width="40%"><?php echo Utilities::getLabel('M_Shipping')?></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product) { $sn++; ?>	
                  <tr id="cart-row-<?php echo md5($product['key']); ?>">
                    <td><div class="pro-image"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($product["product_id"],'THUMB'))?>" alt="<?php echo $product["name"]?>"/></a></div></td>
                    <td><div class="product-name"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><?php echo $product["name"]?></a>
                  <?php if ($product['option']) { ?>
                  <?php foreach ($product['option'] as $option) { ?>
                  <br />
                  - <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                  <?php } ?>
                  <?php } ?>
                  </div>
                  <div class="price"><?php echo Utilities::displayMoneyFormat($product['price'],true,true); ?></div></td>
                     
                    <td>
                    <?php
						if (count($product["shipping_options"])){
							foreach($product["shipping_options"] as $skey=>$sval):
								$country_code=empty($sval["country_code"])?"":" (".$sval["country_code"].")";
								$shipping_charges=$sval["prod_ship_free"]==0?$sval["pship_charges"]:0;
								$shipping_options[$product['product_id']][$skey]=$sval["scompany_name"]." - ".$sval["sduration_label"].$country_code." (+".Utilities::displayMoneyFormat($shipping_charges).")";
							endforeach;
							$select_shipping_options=createDropDownFromArray('shipping_locations['.md5($product['key']).']',$shipping_options[$product['product_id']],$product["shipping_id"],'class="form-control"',''); ?>
                    		<?php echo Utilities::getLabel('M_Select_Shipping')?><br>
	                      <?php echo $select_shipping_options?>
                          
                         <?php } else {?> 
                         	<div class="alert alert-warning">
	                         <?php echo Utilities::getLabel('M_Message_Product_not_available_shipping')?>
							</div>
                         <?php } ?>
                         <span id="ajax_<?php echo $sn?>"></span>
                      </td>
                   <td><a href="javascript:void(0);" onclick="cart.short_remove('<?php echo md5($product['key']); ?>');" class="actions"><img src="<?php echo CONF_WEBROOT_URL?>images/action-icn-delete.png" width="16" height="17" alt="<?php echo Utilities::getLabel('L_Remove')?>"/></a></td>   
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              <div class="total-bottom clearfix">
            	<div class="total-side">
    	          <div class="btn-row"> <a href="javascript:void(0);" id="cartSaveShipping" class="btn primary-btn"> <?php echo Utilities::getLabel('M_Continue')?> </a></div>
               </div>
          	 </div>
             </form>