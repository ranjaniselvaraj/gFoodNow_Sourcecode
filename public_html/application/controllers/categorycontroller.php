<?php
class CategoryController extends CommonController{
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('brands','all'));
	}
	function view($category_id){
		$get = getQueryStringData();
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'feat';
		$conditions_criteria=array("category"=>$category_id,"status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$conditions_criteria);
		$arr_conditions = array_merge($conditions_criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$bObj=new Brands();
		$cObj=new Categories();
		$category=$cObj->getData($category_id,array("status"=>1));
		if (!$category)
			Utilities::show404();
		$cObj->recordCategoryWeightage($category_id);
		$pObj= new Products();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$this->set('total_records', $total_records);
		$child_categories=$cObj->getCategories(array('parent'=>$category['category_id']));
		$sub_categories = array();
		foreach($child_categories as $key=>$val):
				$criteria = array("category"=>$val["category_id"]);
				$pObj= new Products();
				$total_records = $pObj->getProductsCount($criteria);
				$val["category_products"]=$total_records;
				if ($total_records>0):
					$sub_categories[]=$val;
				endif;
		endforeach;
			
		
		$parallel_categories=$cObj->getCategories(array('parent'=>$category['parent'],'type'=>1));
		$child_categories=array("sub_categories"=>$sub_categories,"parallel_categories"=>$parallel_categories);
		$category=array_merge($category,$child_categories);
		$filter_groups = $cObj->getCategoryFiltersDetailed($category_id);
		if ($filter_groups) {
			foreach ($filter_groups as $filter_group) {
				$childen_data = array();
				foreach ($filter_group['filter'] as $filter) {
					$filter_data = array(
						'filter_category_id' => $category_id,
						'filter_filter'      => $filter['filter_id']
						);
					$prObj= new Products();
					$products_count = $prObj->getProductsCount(array_merge(array("category"=>$category_id,"filters"=>$filter['filter_id'],"status"=>1),array("count"=>true)));
					$childen_data[] = array(
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['name'] . ' (' . $products_count . ')'
						);
				}
				$data['filter_groups'][] = array(
					'filter_group_id' => $filter_group['filter_group_id'],
					'name'            => $filter_group['name'],
					'filter'          => $childen_data
					);
			}
		}
		$arr_filter_groups=array("filter_groups"=>$data["filter_groups"]);
		$category=array_merge($category,$arr_filter_groups);
		
		$this->set('category', $category);
		$category_structure=$cObj->funcGetCategoryStructure($category["parent"]);
		if(isset($category_structure[0]) && $category["parent"] == $category_structure[0]['category_id']) {
   			 $category_structure = array_reverse($category_structure);
   	 	}

		$this->set('category_structure',$category_structure);
		$pObj= new Products();
		$price_ranges = $pObj->getProductsMinMaxPrice($conditions_criteria);
		$pObj= new Products();
		$brands = $pObj->getProductsSearchedBrands($conditions_criteria);
		$this->set('brands',$brands);
		$this->set('price_range', $price_ranges);
		$this->set('get',$get);
		$this->_template->render();	
	}
}
