<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          
       	  <h3><?php echo $user_details["user_name"]?>'s <?php echo Utilities::getLabel('L_Favorites')?></h3>
          <ul class="arrowTabs">
            <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"><?php echo Utilities::getLabel('M_Items')?></a></li>
            <li><a href="<?php echo Utilities::generateUrl('account', 'favorite_shops')?>"><?php echo Utilities::getLabel('L_Shops')?></a></li>
          </ul>
          <ul class="fourCols">
            <li>
              <div class="collectionbox">
                <div class="fourthumbs">
                  <?php foreach($favourite_items as $key=>$val): $sn++; if ($sn < 5) { ?>	
	                  <figure class="thumbsquare"><img alt="" src="<?php echo Utilities::generateUrl('image', 'product_image', array($val["prod_id"],'thumb'))?>"></figure>
                  <?php } 
				  endforeach;?>
                </div>
                <div class="bottom"> <span class="collectiontitle"><?php echo Utilities::getLabel('L_Items_i_Love')?></span> <span class="txtcount"><?php echo count($favourite_items)?> <?php echo Utilities::getLabel('M_items')?></span> </div>
                <span class="countpreview"> <a class="linkspan" href="<?php echo Utilities::generateUrl('account','favorite_items')?>"><?php echo Utilities::getLabel('L_View_List')?></a> </span> </div>
            </li>
            
            <?php foreach($user_lists as $key=>$val):?>
            <li>
              <div class="collectionbox">
                <div class="fourthumbs">
                  <?php if (count($val["list_products"])) :
						foreach($val["list_products"] as $listKey=>$listVal):?>
              				<figure class="thumbsquare"><img src="<?php echo Utilities::generateUrl('image','product_image',array($listVal["prod_id"],'thumb'))?>" alt=""></figure>
                       <?php endforeach; else: ?>
                <?php endif;?>
                </div>
                <div class="bottom"> <span class="collectiontitle"><?php echo $val["ulist_title"]?></span>
				<span class="txtcount"><?php echo $val["listProducts"]?> <?php echo Utilities::getLabel('M_items')?></span> </div>
                <span class="countpreview">
               	 <a href="<?php echo Utilities::generateUrl('account','view_list',array($val["ulist_id"]))?>" class="linkspan"><?php echo Utilities::getLabel('L_View_List')?></a><a href="<?php echo Utilities::generateUrl('account','delete_list',array($val["ulist_id"]))?>" class="linkspan"><?php echo Utilities::getLabel('L_Delete_List')?></a>
                
                </span></div>
            </li>
            <?php endforeach;?>
            
            <li>
              <div class="collectionbox">
                <div class="wrapblank">
                                        <div class="createlistform">
                                        	<a href="javascript:void(0)" class="clicklink2 deltelinks"></a>
                                            <?php echo $frm->getFormHtml(); ?>
                                        </div>
                                        
                                        <div class="hidewrap">
                                    		<a href="javascript:void(0)" class="createlink clicklink"><img src="<?php echo CONF_WEBROOT_URL?>images/add_plus.png" alt=""> <span><?php echo Utilities::getLabel('L_Create_New_List')?></span></a>
                                        </div>
                                        
                                    </div>
              </div>
            </li>
            
            
            
          </ul>
        </div>
        
      </div>
    </div>
  </div>
