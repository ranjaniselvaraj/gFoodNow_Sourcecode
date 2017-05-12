<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div> <div class="body clearfix">
    <div class="pageBar">
      <div class="fixed-container">
        <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Brands')?></h1>
      </div>
    </div>
    <div class="innerContainer">
      <div class="greyarea">
        <div class="fixed-container">
          <div class="sitemapcontainer">
            <div class="boxpanel">
              <div class="top">
                <ul class="linksfloated brands">
                  <li><?php echo Utilities::getLabel('L_Browse_Alphabetically')?>:</li>
                  <?php foreach($letters as $key=>$val):?>
                  <li><a lang="custom_div_1" href="<?php echo Utilities::generateUrl('brands','ajax_brands',array(1,$val))?>" class="sitemap_page <?php if ($val==$start_letter): echo 'linkselect'; endif; ?>" ><?php echo $val?></a></li>
                  <?php endforeach;?>
                </ul>
              </div>
              <div class="midd ajax_sitemap scroll" id="custom_div_1" data-href="<?php echo Utilities::generateUrl('brands','ajax_brands',array(1,$start_letter))?>" >	
              </div>  
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  
