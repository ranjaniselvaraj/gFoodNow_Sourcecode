<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div class="body clearfix">
    <?php include CONF_THEME_PATH . $controller.'/_partial/account_subheader.php'; ?>
    <div class="fixed-container">
      <div class="dashboard">
        <?php include CONF_THEME_PATH . $controller.'/_partial/account_leftpanel.php'; ?>
        
        <div class="data-side">
          <?php include CONF_THEME_PATH . $controller.'/_partial/account_tabs.php'; ?>
          <h3><?php echo $user_details["user_name"]?>'s <?php echo Utilities::getLabel('L_List')?></h3>
          <ul class="arrowTabs">
            <li class="active"><a href="<?php echo Utilities::generateUrl('account', 'favorites')?>"><?php echo Utilities::getLabel('M_Items')?></a></li>
            <li><a href="<?php echo Utilities::generateUrl('account', 'favorite_shops')?>"><?php echo Utilities::getLabel('L_Shops')?></a></li>
          </ul>
		  
          <div class="shop-list clearfix">
          	 <div class="list-title"><?php echo $list_details["ulist_title"]?></div>	
          	 <div id="ajax_message"></div> 
             <?php if ($list_items_count>0) {  ?>
					<span id="products-list"></span>
			<?php  }else { ?>
            	<div class="alert alert-info">
	            	<?php echo Utilities::getLabel('L_YOU_NOT_HAVE_ANY_RECORD_LIST')?>
                </div>
            <?php } ?>
                
          </div>
        </div>
        <!--right end--> 
        
      </div>
    </div>
  </div>
<script type="text/javascript">
function filterProducts(page){
	if(processing_product_load == true) return false;
	processing_product_load = true;
	showCssElementLoading($('#products-list'), 1);
	callAjax(generateUrl('account', 'ajax_load_list_products_json',[<?php echo $list_details["ulist_id"]?>]), 'pagesize=30&page='+page, function(response){
			
			loadproducts(response,1);
	});
}
</script> 