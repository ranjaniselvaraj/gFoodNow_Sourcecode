<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php  global $arr_sort_products_options; global $prod_condition; 
$arr_sort = array_merge($arr_sort_products_options,array("relv"=>Utilities::getLabel('L_Relevance')));
$sort_selection =$get['sort']; ?>
<?php 
	$min_start = @$get['min']?$get['min']:$price_range['min_price'];
	$max_start = @$get['max']?$get['max']:$price_range['max_price']; 
?>
<?php echo Utilities::renderHtml($primarySearchForm);?>

<div>
    <div class="body clearfix">
      <div class="breadcrumb">
        <div class="fixed-container">
          <ul>
            <li><a href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Home')?></a></li>
	       	<li><a href="<?php echo Utilities::generateUrl('products') ?>"><?php echo Utilities::getLabel('L_Products')?></a></li>
            <li><?php echo Utilities::getLabel('L_Search_Results')?></li>
          </ul>
        </div>
      </div>
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Search_Results')?></h1>
          <div class="showing-total"><?php echo Utilities::getLabel('L_SHOWING')?> <span id="total_records"><?php echo $total_records?></span></div>
          <div class="filters">
            <ul>
              <?php  foreach($arr_sort as $sortKey=>$sortVal): ?>
				 <li><a class="sort <?php echo (($sort_selection==$sortKey)?'selected_link':''); ?>" href="#" id="<?php echo $sortKey?>"><?php echo $sortVal?></a></li>
			  <?php  endforeach;?>
            </ul>
          </div>
        </div>
      </div>
      <div class="fixed-container">
        <?php  if ($total_records>0):?>
        <form name="search_filters" id="search-filters" >
        <input type="hidden" name="min" value="<?php echo $get['min']?>" data-index="0" class="price_range" id="price_range_lower" />
        <input type="hidden" name="max" value="<?php echo $get['max']?>" data-index="1" class="price_range" id="price_range_upper"/>
        <input type="hidden" name="keyword" value="<?php echo $get['keyword']?>"/>
        <input type="hidden" name="category" id="category_id" value=""/>
        <div class="shop-page">
          <div class="mobile-element">
            <div class="mobile-filter">
              <ul>
              	<li><a href="#"  class="click_trigger" id="ct_4"><i class="icn-filter"> </i><?php echo Utilities::getLabel('L_Filters') ?></a> </li>
                <li><a href="#"  class="click_trigger" id="ct_6"><i class="icn-sort"></i><?php echo Utilities::getLabel('L_Sort') ?></a>
                  <div class="toggle-list" id="list_ct_6">
                    <ul >
                      <?php  foreach($arr_sort as $sortKey=>$sortVal): ?>
						 <li><a class="sort <?php echo (($sort_selection==$sortKey)?'selected_link':''); ?>" href="#" id="<?php echo $sortKey?>"><?php echo $sortVal?></a></li>
					  <?php  endforeach;?>
                    </ul>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          <div class="fixed__panel">
          <div class="left-panel product-filter gray-side" id="fixed__panel">
            <div class="panelLeft left_section">
              <?php  if (count($all_categories)>0):?>	
              <div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Browse_by_categories')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="vertical_links">
                    	<li><ul>
                      <?php  foreach($all_categories as $key=>$val):?>
                     	 <li><a id="<?php echo $val["category_id"]?>" href="#" class="brand_categories <?php if ($val["category_id"]==$get["category"]):?> selected_link <?php endif;?>"><?php echo $val["category_name"]?></a></li>
                     <?php  endforeach;?>
                     	</ul></li>
                    </ul>
                  </div>
                  <div class="clearlink"> <a id="clear_brand_category" href="#"><?php echo Utilities::getLabel('L_Clear_Selection')?></a> </div>
                </div>
              </div>
              <?php  endif;?>
              <div id="brands_box">
				  <?php if (count($brands)>0):?>
                  <div class="boxRound" id="brands_box">
                    <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                      <h4><?php echo Utilities::getLabel('L_Brand')?></h4>
                    </div>
                    <div class="box_Middle toggleWrap">
                      <div class="listscroll">
                        <ul class="labelList ajax-filters" id="ulBrands">
                            <?php foreach($brands as $key=>$val):?>
                            <li>
                            <label><span class="span1"><input name="brand[]" 
                            <?php if (in_array($val["brand_id"],$get["brand"])):?> checked="checked" <?php endif;?> type="checkbox" class="brands" value="<?php echo $val["brand_id"]?>" ></span><span class="span2"><?php echo $val["brand_name"]?></span></label>
                            </li>
                            <?php endforeach;?>
                        </ul>
                      </div>
                      <div class="clearlink"> <a href="#" class="clear_all"><?php echo Utilities::getLabel('L_Clear_All')?></a> </div>
                    </div>
                  </div>
                  <?php endif;?>
              </div> 
              <div id="price_range_box" class="resp_price_range_filter">
              	<?php if (isset($price_range['min_price']) && isset($price_range['max_price'])) { ?>
              	<div class="boxRound box_price_range">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Price')?> <span>(<?php echo CONF_CURRENCY_SYMBOL?>)</span></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="filter-content">
                    <div class="space-lft-right-low marginTop"><div id="slider" class="price_range_slider"></div></div>
                    <div class="prices"></div>
                    <!--<div class="prices"> <span class="from-price-text">Rs 215</span> <span class="to-price-text">Rs 61203</span> </div>-->
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
                  <div class="clearlink"> <a href="#" class="clear_price"><?php echo Utilities::getLabel('L_Clear')?></a> </div>
                </div>
              </div>
              <?php } ?>
              </div>
              
              <?php if (Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING")){?>
              <div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Condition')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="labelList">
	                  <?php foreach($prod_condition as $key=>$val):?>
                      <li>
                        <label><span class="span1">
                          <input class="condition" <?php if (in_array($key,$get["condition"])):?> checked="checked" <?php endif;?>  name="condition[]" type="checkbox" value="<?php echo $key?>"  >
                          </span><span class="span2"><?php echo $val?></span></label>
                      </li>
                      <?php  
					  endforeach;?>
                    </ul>
                  </div>
                  <div class="clearlink"> <a href="javascript:void(0)" class="clear_all"><?php echo Utilities::getLabel('L_Clear_All')?></a> </div>
                </div>
              </div>
              <?php } ?>
              <div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Shipping')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="labelList">
                      <li>
                        <label><span class="span1">
                          <input class="free_shipping" <?php if ($get['property']=='prod_ship_free'):?> checked="checked" <?php endif;?> name="property" value="prod_ship_free" type="checkbox">
                          </span><span class="span2"><?php echo Utilities::getLabel('L_Free_Shipping')?></span></label>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Availability')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="labelList">
                      <li>
                        <label><span class="span1">
                          <input class="out_of_stock" <?php if ($get['out_of_stock']=='1'):?> checked="checked" <?php endif;?> name="out_of_stock" type="checkbox" value="1">
                          </span><span class="span2"><?php echo Utilities::getLabel('L_Exclude_out_of_stock')?></span></label>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
          <div class="right-panel">
            <div class="shop-list clearfix">
	            <span id="products-list"></span>
            </div>
          </div>
        </div>
        </form>
        <?php else:?>
        	<div class=" aligncenter">
          		<div class="no-product">
            		<div class="rel-icon"><img src="<?=CONF_WEBROOT_URL?>images/empty_shopping_bag.png" alt=""></div>
           			 <div class="no-product-txt"> <span><?php echo Utilities::getLabel('L_We_could_not_find_matches')?> </span> <?php echo Utilities::getLabel('L_Try_different_keywords')?></div>
          			  <div class="query-form">
                        <?php echo $SearchForm->getFormHtml();?>
                        	<?php if (count($top_searched_keywords)>0):?>
                                <p class="txt-popular"><strong><?php echo Utilities::getLabel('L_Popular_Searches')?></strong> &nbsp;
                                <?php foreach ($top_searched_keywords as $record) { $inc++;?>
                                    <a href="<?php echo Utilities::generateUrl('products', 'search')?>?keyword=<?php echo $record['searchitem_keyword']?>"><?php echo $record['searchitem_keyword']?> <?php if($inc<count($top_searched_keywords)):?>,<? endif;?></a>
                                <?php } ?>
                                </p>
                           <?php endif;?>
	            		</div>
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
      <?php  if (count($all_categories)>0):?>
      	<li class="tab current"  data-tab="tab-1"><?php echo Utilities::getLabel('L_Categories')?></li>
      <? endif;?>  	
      <?php  if (count($brands)>0):?>	
      	<li class="tab"  data-tab="tab-2"><?php echo Utilities::getLabel('L_Brands')?></li>
      <? endif;?>
      <?php if (isset($price_range['min_price']) && isset($price_range['max_price'])) { ?>
      <li class="tab resp_price_range_filter" data-tab="tab-3"> <?php echo Utilities::getLabel('L_Price')?> <span>(<?php echo CONF_CURRENCY_SYMBOL?>)</span></li>
      <? } ?>
      <li class="tab" data-tab="tab-4"><?php echo Utilities::getLabel('L_Condition')?> </li>
      <li class="tab" data-tab="tab-5"><?php echo Utilities::getLabel('L_Shipping')?> </li>
      <li class="tab" data-tab="tab-6"><?php echo Utilities::getLabel('L_Availability')?> </li>
    </ul>
  </div>
  <div id="tab-1" class="filter-list current">
    <ul>
      <?php  foreach($all_categories as $key=>$val):?>
       	 <li><a id="<?php echo $val["category_id"]?>" href="javascript:void(0);" class="brand_categories"><?php echo $val["category_name"]?></a></li>
	   <?php  endforeach;?>
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