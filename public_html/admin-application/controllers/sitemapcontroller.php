<?php
class SitemapController extends CommonController {
    
	function generate(){
		
		function start_sitemap_xml() {
            ob_start();
            echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        }
        function write_sitemap_url($url, $freq) {
            static $sitemap_i;
            $sitemap_i++;
            if($sitemap_i>2000) {
            $sitemap_i=1;
            end_sitemap_xml();
            start_sitemap_xml();
            }
            echo "
                <url>
                    <loc>".$url."</loc>
                </url>";
            echo "\n";
        }
        function end_sitemap_xml() {
            global $sitemap_list_i;
            $sitemap_list_i++;
            echo '</urlset>' . "\n";
            $contents = ob_get_clean();
            $rs = '';
            Utilities::writeFile('sitemap/list_'.$sitemap_list_i.'.xml', $contents, $rs);
        }
        function write_sitemap_index() {
            global $sitemap_list_i;
            ob_start();
            echo "<?xml version='1.0' encoding='UTF-8'?>
            <sitemapindex xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
                     xsi:schemaLocation='http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd'
                     xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
            for($i=1;$i<=$sitemap_list_i;$i++) {
            echo "<sitemap><loc>".Utilities::getUrlScheme().CONF_WEBROOT_URL."sitemap/list_".$i.".xml</loc></sitemap>\n";
            }
            echo "</sitemapindex>";
            $contents = ob_get_clean();
            $rs = '';
            Utilities::writeFile('sitemap.xml', $contents, $rs);
        }
        /* End of defining functions. */
        start_sitemap_xml();
		/****************  Start Categories ***********************/	
		$criteria=array("status"=>1);
		$categoryObj=new Categories();
		$categories=$categoryObj->getCategories($criteria);
		foreach($categories as $key=>$val){
			write_sitemap_url(Utilities::generateAbsoluteUrl('category', 'view', array($val['category_id']),CONF_WEBROOT_URL), $freq='daily');
		}
		/****************  End Categories ***********************/
		
		
		/****************  Start Produucts ***********************/	
		$productObj= new Products();
		$products=$productObj->getProducts();
		foreach($products as $key=>$val){
				write_sitemap_url(Utilities::generateAbsoluteUrl('products', 'view', array($val['prod_id']),CONF_WEBROOT_URL), $freq='daily');
		}
		/****************  End Produucts ***********************/	
		
		/****************  Start Brands ***********************/	
		$brandObj = new Brands();
		$brands=$brandObj->getBrands(array("status"=>1,"must_products"=>1));
		foreach($brands as $key=>$val){
			write_sitemap_url(Utilities::generateAbsoluteUrl('brands','view',array($val["brand_id"]),CONF_WEBROOT_URL), $freq='daily');
		}
		/****************  End Brands ***********************/
		
		/****************  Start Shops ***********************/	
		$shopObj = new Shops();
		$shops=$shopObj->getShopsByCriteria(array("status"=>1,"must_products"=>1));
		foreach($shops as $key=>$val){
			write_sitemap_url(Utilities::generateAbsoluteUrl('shops','view',array($val["shop_id"]),CONF_WEBROOT_URL), $freq='daily');
		}
		/****************  End Shops ***********************/	
		
		$cmsObj = new Cms();
		$cmspages=$cmsObj->getCmsPages();
		foreach($cmspages as $key=>$val){
	        write_sitemap_url(Utilities::generateAbsoluteUrl('cms', 'view', array($val['page_id']),CONF_WEBROOT_URL), 'monthly');
        }
			
			end_sitemap_xml();
      	    write_sitemap_index();
			Message::addMessage('Success: Sitemap has been updated successfully.');
			Utilities::redirectUserReferer();
	   }
	   
	
}