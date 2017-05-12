<?php
class TagsController extends CommonController{
	function view(){
		$get = getQueryStringData();
		unset($get['tags']);
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'feat';
		$criteria=array("tags"=>(array)$this->tags,"status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = Utilities::createHiddenFormFromArray("primarySearchForm",'',$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$this->set('product_tag', $product_tag);
		$pObj= new Products();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$pObj= new Products();
		$brands = $pObj->getProductsSearchedBrands($criteria);
		$pObj= new Products();
		$price_ranges = $pObj->getProductsMinMaxPrice($criteria);
		$this->set('brands',$brands);
		$this->set('total_records', $total_records);
		$this->set('price_range', $price_ranges);
		$this->set('get',$get);
		$this->_template->render();	
	}
}
