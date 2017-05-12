<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div> <div class="body clearfix">
    <div class="pageBar">
      <div class="fixed-container">
        <h1 class="pageTitle"><?php echo Utilities::getLabel('L_Sitemap')?></h1>
      </div>
    </div>
    <div class="innerContainer">
      <div class="greyarea">
        <div class="fixed-container">
          <div class="sitemapcontainer">
	        <?php if (count($pages)>0):?>
            <h3><?php echo Utilities::getLabel('L_Site_Pages')?></h3>
            <div class="panelborder">
              <ul class="blackbullets">
                <li><a href="<?php echo Utilities::getSiteUrl(); ?>"><?php echo Utilities::getLabel('L_Home')?></a></li>
				<?php foreach($pages as $link): ?>
                    <?php if($link['nl_type']==0): ?>
                    <li><a target="<?php echo $link['nl_target']?>" href="<?php echo Utilities::generateUrl('cms', 'view', array($link['nl_cms_page_id'])); ?>"><?php echo $link['nl_caption']; ?></a></li>
                    <?php elseif($link['nl_type']==3): ?>
                    <li><?php echo $link['nl_caption']; ?></li>
                    <?php elseif($link['nl_type']==2): $url=str_replace('{SITEROOT}', CONF_WEBROOT_URL, $link['nl_html']); 
                        if ($url!=$_SERVER['REQUEST_URI']):
                    ?>
                    <li><a target="<?php echo $link['nl_target']?>" href="<?php echo $url?>"><?php echo $link['nl_caption']; ?></a></li>
                    <?php endif;
                    endif; ?>
                <?php endforeach;?>
              </ul>
            </div>
            <?php endif;?>
            <?php if (count($categories)>0):?>
           		 <h3><?php echo Utilities::getLabel('L_Categories')?></h3>
		            <?php foreach($categories[0] as $key=>$val): $sub_cats=Categories::getCategoriesAssocArrayFront($val["category_id"],1); 
					if (count($sub_cats)>0): ?>
		            <div class="boxpanel">
        		      <div class="top"><span class="txtlink"><a href="<?php echo Utilities::generateUrl('category','view',array($val["category_id"]))?>"><?php echo $val["category_name"]?></a></span></div>
		              <div class="midd">
        			        <ul class="blackbullets">
			                  <?php foreach($categories[$val["category_id"]] as $subkey=>$subval): ?>
            		              <li><a href="<?php echo Utilities::generateUrl('category','view',array($subval["category_id"]))?>"><?php echo $subval["category_name"]?></a></li>
							  <?php endforeach;?>    
            			    </ul>
		               </div>
        	    	</div>
            		<?php endif;?>
				<?php endforeach; ?>
            <?php endif;?>
            
            <h3><?php echo Utilities::getLabel('L_Stores')?></h3>
            <div class="boxpanel">
              <div class="top">
                <ul class="linksfloated stores">
                  <li><?php echo Utilities::getLabel('L_Browse_Alphabetically')?>:</li>
                  <?php foreach($letters as $key=>$val):?>
                  <li><a lang="custom_div_2" href="<?php echo Utilities::generateUrl('shops','ajax_sitemap_shops',array(1,$val))?>" class="<?php if ($val==$start_letter): echo 'linkselect'; endif; ?>" ><?php echo $val?></a></li>
                  <?php endforeach;?>
                </ul>
              </div>
              <div class="midd ajax_sitemap scroll" id="custom_div_2" data-href="<?php echo Utilities::generateUrl('shops','ajax_sitemap_shops',array(1,$start_letter))?>" >	
              </div>
            </div>
            
            <h3><?php echo Utilities::getLabel('L_Brands')?></h3>
            <div class="boxpanel">
              <div class="top">
                <ul class="linksfloated brands">
                  <li><?php echo Utilities::getLabel('L_Browse_Alphabetically')?>:</li>
                  <?php foreach($letters as $key=>$val):?>
                  <li><a lang="custom_div_1" href="<?php echo Utilities::generateUrl('brands','ajax_brands',array(1,$val))?>" class="<?php if ($val==$start_letter): echo 'linkselect'; endif; ?>" ><?php echo $val?></a></li>
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
