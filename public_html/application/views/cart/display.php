<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
    <div class="body clearfix">
      
      <div class="fixed-container">
        <h1 class="page-heading"> <span> <?php echo Utilities::getLabel('M_SHOPPING_CART')?> </span> </h1>
      	
        <div class="cart-box">
          <?php if ($products) { ?>
          <table class="cart-tbl" width="100%" border="0" cellspacing="0" cellpadding="0">
            <thead>
              <tr>
                <th width="15%"><?php echo Utilities::getLabel('M_Image')?></th>
                <th width="20%"><?php echo Utilities::getLabel('M_PRODUCT_NAME')?> </th>
                <th width="15%"><?php echo Utilities::getLabel('M_QTY')?></th>
                <th width="20%"><?php echo Utilities::getLabel('M_Price')?></th>
                <th width="20%"><?php echo Utilities::getLabel('M_SubTotal')?></th>
                <th width="10%"><?php echo Utilities::getLabel('M_Action')?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product) { ?>	
              <tr>
                <td><span class="mobile-caption"><?php echo Utilities::getLabel('M_Image')?></span><div class="pro-image"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><img src="<?php echo Utilities::generateUrl('image','product_image',array($product["product_id"],'THUMB'))?>" alt="<?php echo $product["name"]?>"/> </a></div></td>
                <td><span class="mobile-caption"><?php echo Utilities::getLabel('M_PRODUCT_NAME')?></span><div class="product-name"><a href="<?php echo Utilities::generateUrl('products','view',array($product["product_id"]))?>"><?php echo $product["name"]?></a><?php if (!$product['stock'] && Settings::getSetting("CONF_CHECK_STOCK")) { ?>
                  <span class="text-danger">***</span>
                  <?php } ?><?php if ($product['self_product'] && !Settings::getSetting("CONF_ENABLE_BUYING_OWN_PRODUCTS")) { ?>
                  <span class="text-danger">&#9940;</span>
                  <?php } ?>
                  <?php if ($product['option']) { ?>
                  <?php foreach ($product['option'] as $option) { ?>
                  <br />
                  - <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                  <?php } ?>
                  <?php } ?><?php if ($product['shipping_free']) { ?>
                		 <br />
			                 - <small><?php echo Utilities::getLabel('L_Free_Shipping')?></small>
	                  <?php } ?>
                  </div></td>
                <td><span class="mobile-caption"><?php echo Utilities::getLabel('M_QTY')?></span><div class="qty">
                    <input type="text" id="qty<?php echo md5($product['key']); ?>" name="quantity[<?php echo md5($product['key']); ?>]"  maxlength="3" value="<?php echo $product['quantity']; ?>" class="form-control inline-block">
                    <a href="javascript:void(0);" onclick="cart.update('<?php echo md5($product['key']); ?>','<?php echo md5($product['key']); ?>');" class="actions"><img alt="" src="<?php echo CONF_WEBROOT_URL?>images/refresh-16.png"></a> </div></td>
                <td nowrap="nowrap"><span class="mobile-caption"><?php echo Utilities::getLabel('M_Price')?></span><div class="price"><?php echo Utilities::displayMoneyFormat($product['price'],true,true); ?></div></td>
	            <td nowrap="nowrap"><span class="mobile-caption"><?php echo Utilities::getLabel('M_SubTotal')?></span><div class="sub-total"><?php echo Utilities::displayMoneyFormat($product['total'],true,true); ?></div></td> 
                <td><span class="mobile-caption"><?php echo Utilities::getLabel('M_Action')?></span><a href="javascript:void(0);" onclick="cart.remove('<?php echo md5($product['key']); ?>');" class="actions"><img src="<?php echo CONF_WEBROOT_URL?>images/action-icn-delete.png" width="16" height="17" alt="<?php echo Utilities::getLabel('L_Remove')?>"/></a></td>
            </tr>
              <?php } ?>
              
            </tbody>
          </table>
		
        <div class="total-bottom clearfix">
       		 <div class="total-money">
                <div class="amount-desc">
                  <ul>
                    <li><?php echo Utilities::getLabel('M_Cart_Total')?> <span class="figure"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_total"]); ?></span></li>
                    <?php if (($cart_summary["cart_tax_total"]>0)){?>
                    <li><?php echo Utilities::getLabel('M_Tax')?><span class="figure"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_tax_total"]); ?></span></li>
                    <?php } ?> 
                    
                    <li class="total"><?php echo Utilities::getLabel('M_Total')?> <span class="figure"><?php echo Utilities::displayMoneyFormat($cart_summary["cart_total"]+$cart_summary["cart_tax_total"],true,true); ?></span></li>
                    <!--<li>Payment Due <span class="figure due"><i class="currency">USD</i> $ 359.89</span></li>-->
                  </ul>
                </div>
                
              </div>
              <div class="btn-row"> <a href="<?php echo Utilities::getSiteUrl()?>" class="btn secondary-btn"><?php echo Utilities::getLabel('M_Continue_Shopping')?></a>  <a href="<?php echo Utilities::generateUrl('cart','checkout')?>" class="btn primary-btn "> <?php echo Utilities::getLabel('M_Checkout')?> </a></div>		
           </div>  	
          
        <?php }else{ ?>
		<!--<div class="text-info text-center">Your shopping cart is empty!</div>-->
        <div class="alert alert-warning"><?php echo Utilities::getLabel('M_Your_cart_is_empty')?></div>
        
        <div class="empty-listing option-2">
          <ul>
          	<?php foreach ($empty_cart_items as $item) { $inc++; $mod=$inc%5; if ($mod==0) { $inc=1; $mod=1; }
					$empty_cart_item_url=str_replace('{SITEROOT}', CONF_WEBROOT_URL, $item['emptycartitem_url']); 
					$word_arr=explode(" ",$item['emptycartitem_title']);
					$wordinc=0;
					$word_1=$word_arr[0];
					$word_2="";
					if (strlen($word_arr[1]) < 3){
						$word_1.=' '.$word_arr[1];
						$wordinc = 1;
					}
					for ($k=$wordinc; $k <= count($word_arr); $k++  ){
						$word_2.=$word_arr[$k+1]." ";
					}
				?>
            <li>
              <a target="<?php echo $item['emptycartitem_link_newtab']?"_blank":"_self"?>" href="<?php echo $empty_cart_item_url?>"><div class="empty-pic"><img src="<?php echo CONF_WEBROOT_URL?>images/s-empty-banner-<?php echo $mod?>.jpg" alt="<?php echo $item['emptycartitem_title'] ?>"/></div>
              <div class="txt"><?php echo $word_1;?> <span><?php echo $word_2?></span></div></a>
            </li>
            <? } ?>
          </ul>
        </div>
        
      <?php }?>
      </div>
      
      
      <div class="gap"></div>
        
        <?php if ($cart_also_bought_products) {?>
        <div class="section category-list" >
        	    <div class="main-heading">
	        	    <h3><?php echo Utilities::getLabel('L_Customers_who_bought_this_also_bought') ?></h3>
    	        </div>
                <div class="shop-list products-carousel <? if (count($cart_also_bought_products)<6):?>less_items<?php endif; ?>">
		            <?php  foreach ($cart_also_bought_products as $product) { $inc++;  { 
					           include CONF_THEME_PATH . 'common/product_thumb_view.php';
				           }
		            }  ?>
        	    </div>
            </div>
        <?php } ?>
      
    </div>
    </div>
    
  </div>
  