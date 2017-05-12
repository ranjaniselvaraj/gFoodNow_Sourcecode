<div class="fixed__panel">
<div class="left-panel gray-side" id="fixed__panel">
			<?php if ((count($shop_categories)>0) && !empty($shop_categories)):?>
            <div class="left-nav">
              	<div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('M_Shop_Sections')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="labelList">
                      <?php foreach($shop_categories[$category] as $shopkey=>$shopval):?>
                      	<li><a href="<?php echo Utilities::generateUrl('shops','view',array($shop["shop_id"],$shopval["category_id"]))?>"><?php echo $shopval["category_name"]?> (<?php echo $shopval["category_products"]?>)</a></li>
                     <?php endforeach;?>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <?php endif;?>
            
            <?php if (($controller=="shops") && ($action=="view")) {?>
            <div class="panelLeft left_section">
              
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
	                  <?php foreach($prod_condition as $key=>$val): //if (in_array($key,$conditions)):?>
                      <li>
                        <label><span class="span1">
                          <input class="condition" name="condition[]" type="checkbox" value="<?php echo $key?>" >
                          </span><span class="span2"><?php echo $val?></span></label>
                      </li>
                      <?php //endif; 
					  endforeach;?>
                    </ul>
                  </div>
                  <div class="clearlink"> <a href="javascript:void(0)" class="clear_all"><?php echo Utilities::getLabel('L_Clear_All')?></a> </div>
                </div>
              </div>
              <?php } ?>
              <div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Availability')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="labelList">
                      <li>
                        <label><span class="span1">
                          <input class="out_of_stock" name="out_of_stock" type="checkbox" value="1">
                          </span><span class="span2"><?php echo Utilities::getLabel('L_Exclude_out_of_stock')?></span></label>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <?php }?>
            
            
            
            
            
            <div class="widget">
              <ul class="left-listing">
                <li><a href="<?php echo Utilities::generateUrl('shops','send_message',array($shop["shop_id"]))?>"><?php echo Utilities::getLabel('M_Send_Message')?></a></li>
               
                <li><a href="<?php echo Utilities::generateUrl('shops', 'report',array($shop["shop_id"]))?>"><?php echo Utilities::getLabel('L_Report_this_shop')?></a></li>
                <li><a href="<?php echo Utilities::generateUrl('shops','policies',array($shop["shop_id"]))?>"><?php echo Utilities::getLabel('M_Policies')?></a></li>
              </ul>
            </div>
            <?php if (Settings::getSetting("CONF_ALLOW_REVIEWS")):?>
              <div class="review-wrapper">	
              <a href="<?php echo Utilities::generateUrl('shops','reviews',array($shop["shop_id"]))?>"><?php echo Utilities::getLabel('M_Reviews')?> </a>
              <div class="reviewlist">
                <ul class="rating">
                  <?php for($j=1;$j<=5;$j++){ ?>	
                    <li class="<?php echo $j<=round($shop["shop_rating"])?"active":"in-active" ?>"> 
                    <svg xml:space="preserve" enable-background="new 0 0 70 70" viewBox="0 0 70 70" height="18px" width="18px" y="0px" x="0px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" id="Layer_1" version="1.1">
                     <g>
                        <path d="M51,42l5.6,24.6L35,53.6l-21.6,13L19,42L0,25.4l25.1-2.2L35,0l9.9,23.2L70,25.4L51,42z M51,42" 
                         fill="<?php echo $j<=round($shop["shop_rating"])?"#ff3a59":"#474747" ?>" />
                     </g>
                </svg> </li>
                <?php } ?>  
                </ul>
                <p><?php echo $shop["shop_rating"]?> <?php echo Utilities::getLabel('M_out_of')?> 5</p>
              </div>
              </div>
               <?php endif; ?>
          </div>
         </div> 