<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php  global $arr_sort_products_options; global $prod_condition; ?>
<div>
    <div class="body clearfix">
      <div class="breadcrumb">
        <div class="fixed-container">
          <ul>
            <li><a href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Home') ?></a></li>
            <?php foreach($product["product_category"] as $pcat){?>
            	<li><a href="<?php echo Utilities::generateUrl('category','view',array($pcat["category_id"]))?>"><?php echo $pcat["category_name"]?></a></li>	
            <?php };?>
            <li><?php echo $product["prod_name"]?></li>
          </ul>
        </div>
      </div>
      <div class="pageBar">
        <div class="fixed-container">
          <h1 class="pageTitle"><?php echo $product["prod_name"]?></h1>
          <div class="fr"> <a href="<?php echo Utilities::generateUrl('products', 'view',array($product["prod_id"]))?>" class="btn small">&laquo;&laquo; <?php echo Utilities::getLabel('L_Back_to_product_page')?></a> </div>
        </div>
      </div>
      <div class="fixed-container">
        <div class="shop-page">
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
		                     	 <li><a href="<?php echo Utilities::generateUrl('category','view',array($val["category_id"]))?>"><?php echo $val["category_name"]?></a></li>
        		             <?php  endforeach;?>
                     	</ul></li>
                    </ul>
                  </div>
                </div>
              </div>
              <?php  endif;?> 
              <?php  if (count($brands)>0):?>	
              <div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Brands')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="vertical_links">
                    	<li><ul>
                     		 <?php  foreach($brands as $key=>$val):?>
		                     	 <li><a href="<?php echo Utilities::generateUrl('brands','view',array($val["brand_id"]))?>"><?php echo $val["brand_name"]?></a></li>
        		             <?php  endforeach;?>
                     	</ul></li>
                    </ul>
                  </div>
                </div>
              </div>
              <?php  endif;?> 
            </div>
          </div>
          </div>
          <div class="right-panel">
                <div class="wrapform">
				<?php echo $frm->getFormHtml();?></div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
  
