<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php  global $arr_sort_products_options; global $prod_condition; $sort_selection =$get['sort']; ?>
<?php 
	$min_start = @$get['min']?$get['min']:$price_range['min_price'];
	$max_start = @$get['max']?$get['max']:$price_range['max_price']; 
?>
<?php echo Utilities::renderHtml($primarySearchForm);?>
<form name="search_filters" id="search-filters"/>
<input type="hidden" name="min" value="<?php echo $get['min']?>" data-index="0" class="price_range" id="price_range_lower" />
<input type="hidden" name="max" value="<?php echo $get['max']?>" data-index="1" class="price_range" id="price_range_upper"/>
<div>
    <div class="body clearfix">
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Top_50_Best_Selling_Products')?></h1>
        </div>
      </div>
      <div class="fixed-container">
        <?php  if ($total_records>0):?>
        <div class="shop-page">
          <div class="mobile-element">
            <div class="mobile-filter">
              <ul>
              	<li><a href="#"  class="click_trigger" id="ct_4"><i class="icn-filter"> </i><?php echo Utilities::getLabel('L_Filters') ?></a> </li>
              </ul>
            </div>
          </div>
          	
          <div class="shop-list clearfix">
	          <span id="products-list"></span>
          </div>
          
        </div>
        <?php else:?>
    	  	<div class="aligncenter">
          		<div class="no-product">
            		<div class="rel-icon"><img src="<?=CONF_WEBROOT_URL?>images/empty_shopping_bag.png" alt=""></div>
        			<div class="no-product-txt"> <span><?php echo Utilities::getLabel('L_We_could_not_find_matches')?> </span> <?php echo Utilities::getLabel('L_Try_different_keywords_filters')?></div>
	          </div>
        	</div>
      	<?php endif?>
      </div>
    </div>
  </div>
  
  
<div class="filter-popup no-action" id="list_ct_4">
  <div class="back-header"><a  href="#" class="icn-back">
    <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="70px" height="70px" viewBox="-364 606 70 70" enable-background="new -364 606 70 70" xml:space="preserve">
      <path fill="#ffffff" d="M-323.3,610.7l-4.6-4.7l-36.1,34.9l36.1,34.9l4.6-4.7l-31.3-30.2L-323.3,610.7z"/>
    </svg>
    </a> <span class="linkcaption"><?php echo Utilities::getLabel('L_Filters')?></span></div>
  <div class="filter-option">
    <ul class="tabs tab-no-action">
      <?php  if (count($brands)>0):?>	
      	<li class="tab current"  data-tab="tab-2"><?php echo Utilities::getLabel('L_Brands')?></li>
      <? endif;?>
      <?php if (isset($price_range['min_price']) && isset($price_range['max_price'])) { ?>
      <li class="tab resp_price_range_filter" data-tab="tab-3"> <?php echo Utilities::getLabel('L_Price')?> <span>(<?php echo CONF_CURRENCY_SYMBOL?>)</span></li>
      <? } ?>
      <li class="tab" data-tab="tab-4"><?php echo Utilities::getLabel('L_Condition')?> </li>
      <li class="tab" data-tab="tab-5"><?php echo Utilities::getLabel('L_Shipping')?> </li>
      <li class="tab" data-tab="tab-6"><?php echo Utilities::getLabel('L_Availability')?> </li>
    </ul>
  </div>
 <div id="tab-2" class="filter-list">
    <ul>
      <?php  foreach($brands as $key=>$val):?>	
      <li><label><span class="facetoption">
        <input name="brand[]" type="checkbox" class="brands faceoption" value="<?php echo $val["brand_id"]?>" >
        </span> <span class="filter-name"><?php echo $val["brand_name"]?></span> </label></li>
      <? endforeach; ?> 
    </ul>
  </div>
  <?php if (isset($price_range['min_price']) && isset($price_range['max_price'])) { ?>
  <div  id="tab-3" class="filter-list">
  		<div class="resp_price_range_filter">
        	<div class="filter-content">
                    <div class="space-lft-right-low marginTop"><div class="price_range_slider"></div></div>
                    <div class="prices"></div>
                    <div class="clear"></div>
                    <div class="price-input">
                      <div class="price-text-box">
                        <input class="input-filter form-control" readonly="readonly" data-index="0" min="<?php echo $min_start?>" max="<?php echo $max_start?>" value="<?php echo $min_start?>">
                        </div>
                    </div>
                    <span class="dash"> - </span>
                    <div class="price-input">
                      <div class="price-text-box">
                        <input class="input-filter form-control" readonly="readonly" data-index="1" min="<?php echo $min_start?>" max="<?php echo $max_start?>" value="<?php echo $max_start?>">
                        </div>
                    </div>
                    </div>
         </div>           
  </div>
  <?php } ?>
  
  <div id="tab-4" class="filter-list">
    <ul>
      <?php  foreach($prod_condition as $key=>$val): ?>  
      <li><label><span class="facetoption">
        <input class="condition faceoption" name="condition[]" type="checkbox" value="<?php echo $key?>">
        </span> <span class="filter-name"><?php echo $val?></span> </label></li>
       <? endforeach;?> 
    </ul>
  </div>
  <div id="tab-5" class="filter-list">
    <ul>
      <li><label><span class="facetoption">
        <input class="free_shipping faceoption" name="property" value="prod_ship_free"  type="checkbox">
        </span> <span class="filter-name"><?php echo Utilities::getLabel('L_Free_Shipping')?></span> </label></li>
    </ul>
  </div>
  <div id="tab-6" class="filter-list">
    <ul>
      <li><label><span class="facetoption">
        <input class="out_of_stock faceoption" name="out_of_stock" type="checkbox" value="1">
        </span> <span class="filter-name"><?php echo Utilities::getLabel('L_Exclude_out_of_stock')?></span> </label></li>
    </ul>
  </div>
  
</div>  
</form>