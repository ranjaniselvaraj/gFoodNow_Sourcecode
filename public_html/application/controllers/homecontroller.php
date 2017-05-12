<?php
class HomeController extends CommonController
{
	function view_cookie()
	{
		echo ("R - " . $_COOKIE['referrer_tracking'] . "<br/>");
		echo ("A - " . $_COOKIE['tracking']);
		die();
	}
	function default_action()
	{
		require_once (CONF_INSTALLATION_PATH . 'public/includes/phpfastcache.php');
		Syspage::addJs(array(
			'js/owl.carousel.js'
			) , false);
		Syspage::addJs(array(
			'js/slick.min.js'
			) , false);
		Syspage::addCss(array(
			'css/slick.css'
			) , false);
// simple Caching with:
		phpFastCache::setup("storage", "files");
		phpFastCache::setup("path", CONF_USER_UPLOADS_PATH . "caching");
		$prmObj = new Promotions();
		$arr = array(
			"front" => 1,
			"type" => 3,
			"status" => 1,
			"position" => "TB",
			"pagesize" => 5,
			"order_by" => 'random'
			);
		$promotion_header_banners = $prmObj->getPromotions($arr);
		$arr = array(
			"front" => 1,
			"type" => 3,
			"status" => 1,
			"position" => "BB",
			"pagesize" => 4,
			"order_by" => 'random'
			);
		$promotion_bottom_banners = $prmObj->getPromotions($arr);
		/*Utilities::printarray($promotion_bottom_banners);
		die();*/
		$pObj= new Products();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$smart_recommended_products = $pObj->getSmartRecommendedProducts(0);
		$pObj= new Products();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$recently_viewed_products = $pObj->getRecentlyViewedProducts($this->getLoggedUserId());
		$pObj= new Products();
		$ppc_products = $pObj->getPPCProducts();
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$featured_products = $pObj->getFeaturedProducts();
		$shopObj = new Shops();
		$ppc_shops = $shopObj->getPPCShops();
		$cache = phpFastCache();
		$home_page_elements = $cache->get("home_page_elements");
		if ($home_page_elements == null) {
			$slideObj = new Slides();
			$home_slides = $slideObj->getHomePageSlides();
			$bannerObj = new Banners();
			$home_banners = $bannerObj->getHomePageBanners();
			$collectionObj = new Collections();
			$home_page_collections = $collectionObj->getCollections(array(
				"status" => 1,
				"front_end" => 1
				));
			$home_page_elements = array(
				"slides" => $home_slides,
				"banners" => $home_banners,
				"collections" => $home_page_collections
			);
			$cache->set("home_page_elements", $home_page_elements, 12 * 60 * 60); // 12 Hours Cacheing
		}
		$this->set('home_page_elements', $home_page_elements);
		$this->set('smart_recommended_products', $smart_recommended_products);
		$this->set('promotion_header_banners', $promotion_header_banners);
		$this->set('recently_viewed_products', $recently_viewed_products);
		$this->set('promotion_bottom_banners', $promotion_bottom_banners);
		$this->set('featured_products', $featured_products);
		$this->set('ppc_products', $ppc_products);
		$this->set('ppc_shops', $ppc_shops);
		$this->_template->render();
	}
function referral($referrer_tracking_code)
{
	if (isset($referrer_tracking_code)) {
		setcookie('referrer_tracking', $referrer_tracking_code, time() + 3600 * 24 * 1000, '/');
		setcookie('tracking', '', time() - 3600, '/');
		Utilities::redirectUser(Utilities::getSiteUrl());
	}
	else {
		die("Invalid Action");
	}
}
}
