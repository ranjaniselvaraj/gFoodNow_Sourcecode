<?php
class BrandsController extends CommonController{
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('brands','all'));
	}
	function view($id){
		$brand=$this->Brands->getData($id,array("status"=>1));
		if (!$brand)
			Utilities::show404();
		$this->Brands->recordBrandWeightage($id);
		$get = getQueryStringData();
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'feat';
		$criteria=array("brand"=>$id,"status"=>1,"pagesize"=>$pagesize,"page"=>1,"brand_page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$pObj= new Products();
		$categories = $pObj->getProductsSearchedCategories($criteria);
		$pObj= new Products();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$pObj= new Products();
		$price_ranges = $pObj->getProductsMinMaxPrice($criteria);
		$this->set('total_records', $total_records);	
		$this->set('brand', $brand);
		$this->set('all_categories',$categories);
		$this->set('total_records', $total_records);
		$this->set('price_range', $price_ranges);
		$this->set('get',$get);
		$this->_template->render();	
	}
	function all($id){
		$letters=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$this->set('letters', $letters);
		$this->_template->render();	
	}
	function ajax_brands($paging_page,$letter){
		$page = 1;
		if(isset($paging_page) && intval($paging_page) > 0) $page = intval($paging_page); 
		$pagesize = 40;
		$arr=$this->Brands->getBrands(array("status"=>1,"page"=>$page,"pagesize"=>$pagesize,"start"=>$letter,"order"=>"brand_name"));
		$this->set('brands', $arr);
		$this->set('pages', $this->Brands->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $this->Brands->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('letter', $letter);
		$this->_template->render(false,false);
	}
}
