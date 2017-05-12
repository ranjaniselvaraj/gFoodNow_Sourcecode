<?php
class Categories extends Model{
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCategoryId() {
        return $this->category_id;
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getError() {
        return $this->error;
    }
	
	function getCategoriesHavingRecords($type) {
		$srch = new SearchBase('tbl_categories', 'tc');
		$srch->addCondition('tc.category_is_deleted', '=', 0);
		$srch->addCondition('tc.category_type', '=', (int)$type);
		if ($type==1)
			$srch->joinTable('tbl_products', 'INNER  JOIN', 'tc.category_id=tp.prod_category and prod_is_deleted=0 and prod_status=1', 'tp');
		elseif ($type==2)
			$srch->joinTable('tbl_faqs', 'INNER  JOIN', 'tc.category_id=tf.faq_category_id and tf.faq_is_deleted=0', 'tf');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tc.category_id');
		$srch->addMultipleFields(array('tc.category_id','tc.category_name',"count(*) as totRecords"));
		$srch->addHaving('totRecords','>',0);
		$srch->addCondition('tc.category_status', '=',1);
		$srch->addOrder('tc.category_display_order', 'asc');
		$rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	
	function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getParentAssociativeArray($parent_id=0,$category_type) {
		$srch = new SearchBase('tbl_categories', 'tc');
		$srch->addCondition('category_is_deleted', '=',0); 
		$srch->addCondition('category_parent', '=',$parent_id); 
		if (!empty($category_type))
		$srch->addCondition('category_type', '=',$category_type); 
		$srch->addOrder('category_display_order');
		$srch->addMultipleFields(array('category_id','category_name'));
		$rs = $srch->getResultSet();
        return $this->db->fetch_all_assoc($rs);
    }
	
	static function getCatgeoryTreeStructure($parent_id=0,$keywords='',$level=0,$name_prefix='',$category_type=1) {
		$seprator = ''; $category_name = '';
        $db = &Syspage::getdb();
        $query = "SELECT category_id, category_name FROM tbl_categories WHERE category_status=1 and category_is_deleted=0 and category_parent='{$parent_id}' and category_type='{$category_type}'";
        if (!empty($keywords)) $query .= " AND category_name like '%$keywords%'";
        $query .= '  order by category_display_order,category_name';
        $rs = $db->query($query);
        $categories = $db->fetch_all_assoc($rs);
        $return = array();
		if ($level>0)
			$seprator=' &nbsp;&nbsp;&raquo;&raquo;&nbsp;&nbsp; ';
				
        foreach ($categories as $category_id=>$category_name) {
			$name=	$name_prefix .$seprator. $category_name;
            $return[$category_id] = $name;
            $return += self::getCatgeoryTreeStructure($category_id, $keywords, $level+1,$name,$category_type);
        }
        return $return;
    }
	
	function getCategoriesAssocArrayFront($parent_id=0, $type=1, $pagesize=100) {
        $db = &Syspage::getdb();
		
		$srch = new SearchBase('tbl_categories', 'tc');
		$srch->addCondition('tc.category_is_deleted', '=',0); 
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tc.category_parent');
		$srch->addMultipleFields(array('tc.category_parent',"COUNT(tc.category_id) AS total_sub_cats"));
		$qry_subcats_received = $srch->getQuery();
		
        $query = "SELECT tc.*,IFNULL(tcs.total_sub_cats,0) as subcats FROM tbl_categories tc LEFT JOIN (".$qry_subcats_received.") tcs on tcs.category_parent=tc.category_id WHERE category_status=1 and category_is_deleted=0 and category_type='{$type}' and tc.category_parent='{$parent_id}'";
        $query .= ' order by category_display_order,category_name';
		if ($pagesize)
			$query .= " limit 0, $pagesize";
		//die($query);
        $rs = $db->query($query);
        $categories = $db->fetch_all($rs);
        $return = array();
        foreach ($categories as $catkey=>$catval) {
			$criteria = array("category"=>$catval["category_id"]);
			$pObj= new Products();
			$total_records = $pObj->getProductsCount($criteria);
			if ($total_records>0){	
	            $return[$parent_id][$catval["category_id"]] = $catval;
    	        $return += self::getCategoriesAssocArrayFront($catval['category_id'], $type,100);
			}
        }
        return $return;
    }
	
	static function getProdClassifiedCategoriesAssocArray($parent_id=0, $not_to_include=false, $level=0) {
        $db = &Syspage::getdb();
        $query = "SELECT category_id, category_name FROM tbl_categories WHERE category_status=1 and category_is_deleted=0 and category_parent='{$parent_id}'";
        if (!empty($not_to_include)) $query .= " AND category_id NOT IN ({$not_to_include})";
        $query .= '  order by category_display_order,category_name';
        $rs = $db->query($query);
        $categories = $db->fetch_all_assoc($rs);
        $return = array();
        $category_name_prefix = '';
        for($i=0;$i<$level;$i++) {
            $category_name_prefix .= '-- ';
        }
        foreach ($categories as $category_id=>$category_name) {
            $return[$category_id] = $category_name_prefix . $category_name;
            $return += self::getProdClassifiedCategoriesAssocArray($category_id, $not_to_include, $level+1);
        }
        return $return;
    }
	
	/*function funcGetCategoryStructure($category_id,$category_tree_array = ''){
		if (!is_array($category_tree_array)) $category_tree_array = array();
		$categories_query = $this->db->query("select * from tbl_categories  where category_id = '" . (int)$category_id . "' and category_status=1 and category_is_deleted=0 order by category_display_order,category_name");
		while($categories=$this->db->fetch($categories_query)){
		  $category_tree_array[] = $categories;
    	  $category_tree_array = self::funcGetCategoryStructure($categories['category_parent'],$category_tree_array);
    	}
		sort($category_tree_array);
		return $category_tree_array;
	}*/
	
	function funcGetCategoryStructure($category_id,$category_tree_array = ''){
			if (!is_array($category_tree_array)) $category_tree_array = array();
			   	$categories_query = $this->db->query("select * from tbl_categories  where category_id = '" . (int)$category_id . "' and category_status=1 and category_is_deleted=0 order by category_display_order,category_name");
   	 			while($categories=$this->db->fetch($categories_query)){
				   	   $category_tree_array[] = $categories;
   	   		  	   	   $category_tree_array = self::funcGetCategoryStructure($categories['category_parent'],$category_tree_array);
   	 			}
		   	 $category_tree_array = array_reverse($category_tree_array);  
		  	 return $category_tree_array;
    }
	
	function getCategoryFilters($category_id) {
		$category_filter_data = array();
		$rs = $this->db->query("SELECT * FROM tbl_category_filter tcf INNER JOIN tbl_filters tf on tcf.filter_id=tf.filter_id  WHERE category_id = '" . (int)$category_id . "' order by filter_group asc");
		while ($row=$this->db->fetch($rs)){
			$category_filter_data[] = $row['filter_id'];
		}
		return $category_filter_data;
	}
	
	function getCategoryFiltersDetailed($category_id) {
		$implode = array();
		$rs = $this->db->query("SELECT filter_id FROM tbl_category_filter WHERE category_id = '" . (int)$category_id . "'");
		while ($row=$this->db->fetch($rs)){
			$implode[] = (int)$row['filter_id'];
		}
		$filter_group_data = array();
		if ($implode) {
			$filter_group_query = $this->db->query("SELECT DISTINCT f.filter_group, fg.filter_group_name, fg.filter_group_display_order FROM tbl_filters f LEFT JOIN tbl_filter_groups fg ON (f.filter_group = fg.filter_group_id) WHERE f.filter_id IN (" . implode(',', $implode) . ") AND f.filter_is_deleted=0 and fg.filter_group_is_deleted=0 GROUP BY f.filter_group  ORDER BY fg.filter_group_display_order, LCASE(fg.filter_group_name)");
			//foreach ($filter_group_query->rows as $filter_group) {
			while ($filter_group=$this->db->fetch($filter_group_query)){
				$filter_data = array();
				$filter_query = $this->db->query("SELECT DISTINCT f.filter_id, f.filter_name FROM tbl_filters f WHERE f.filter_id IN (" . implode(',', $implode) . ") AND f.filter_group = '" . (int)$filter_group['filter_group'] . "' AND f.filter_is_deleted=0 ORDER BY f.filter_display_order, LCASE(f.filter_name)");
				//foreach ($filter_query->rows as $filter) {
				while ($filter=$this->db->fetch($filter_query)){	
					$filter_data[] = array(
						'filter_id' => $filter['filter_id'],
						'name'      => $filter['filter_name']
					);
				}
				if ($filter_data) {
					$filter_group_data[] = array(
						'filter_group_id' => $filter_group['filter_group'],
						'name'            => $filter_group['filter_group_name'],
						'filter'          => $filter_data
					);
				}
			}
		}
		return $filter_group_data;
		
	}
	
	function getProductCategoriesHavingRecords($parent,$shop) {
			$categories=Categories::getCategoriesAssocArrayFront($parent,1);
			foreach($categories[$parent] as $key=>$val):
				$criteria = array("shop"=>$shop,"category"=>$val["category_id"]);
				$pObj= new Products();
				$total_records = $pObj->getProductsCount($criteria);
				$val["category_products"]=$total_records;
				if ($total_records>0):
					$shop_categories[]=$val;
				endif;
			endforeach;
			$return = array();
			foreach ($shop_categories as $catkey=>$catval) {
					$category_id=$catval["category_id"];
        		    $return[$parent][$category_id] = $catval;
		            $return += self::getProductCategoriesHavingRecords($category_id, $shop);
       		}
			return $return;	
	}
	
	
	function getCategories($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('category_status','desc');
		$srch->addOrder('tcat.category_display_order','asc');
		$srch->addOrder('tcat.category_name','asc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	function search($criteria) {
		$srch = new SearchBase('tbl_categories', 'tc');
		$srch->addCondition('tc.category_is_deleted', '=',0); 
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tc.category_parent');
		$srch->addMultipleFields(array('tc.category_parent',"COUNT(tc.category_id) AS total_sub_cats"));
		$qry_subcats_received = $srch->getQuery();
		
        $srch = new SearchBase('tbl_categories', 'tcat');
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tcat.category_id = REPLACE(tua.url_alias_query,"category_id=","")', 'tua');
		$srch->addCondition('tcat.category_is_deleted', '=', 0);
        $srch->joinTable('tbl_categories', 'left outer join', 'tcat.category_parent=tcatsub.category_id', 'tcatsub');
		$srch->joinTable('(' . $qry_subcats_received . ')', 'LEFT OUTER JOIN', 'tcat.category_id = tqsubcat.category_parent', 'tqsubcat');
		$srch->addMultipleFields(array('tcat.*','tua.url_alias_keyword as seo_url_keyword','tcat.category_parent as parent', 'tcatsub.category_name category_parent', 'tcatsub.category_parent category_parent_id','IFNULL(tqsubcat.total_sub_cats,0) as subcats'));
		foreach($criteria as $key=>$val) {
        //if(strval($val)=='') continue;
        switch($key) {
		case 'id':	
        case 'category_id':
            $srch->addCondition('tcat.category_id', '=', intval($val));
            break;
		case 'category_parent_id':
            $cnd=$srch->addCondition('tcat.category_id', '=', intval($val));
			$cnd->attachCondition("tcat.category_parent", '=',$val,"OR");
            break;		
		case 'type':
            $srch->addCondition('tcat.category_type', '=', intval($val));
            break;
		case 'parent':
            $srch->addCondition('tcat.category_parent', '=', intval($val));
            break;	
		case 'status':
            $srch->addCondition('tcat.category_status', '=', intval($val));
            break;	
		case 'featured':
            $srch->addCondition('tcat.category_featured', '=', intval($val));
            break;
		case 'keyword':
            $srch->addCondition('tcat.category_name', 'like', '%'.$val.'%');
            break;	
		case 'page':
				$srch->setPageNumber($val);
			break;	
		case 'pagesize':
				$srch->setPageSize($val);
			break;	
		case 'slug':
		    $srch->addCondition('tcat.category_slug', '=', ($val));
            break;		
	        }
        }
		return $srch;	
    }
	
	function addUpdate($data){
		$category_id = intval($data['category_id']);
		$record = new TableRecord('tbl_categories');
		$assign_fields = array();
		$assign_fields['category_type'] = intval($data['category_type']);
		$assign_fields['category_name'] = $data['category_name'];
		$assign_fields['category_slug'] = $data['category_slug'];
		$assign_fields['category_description'] = $data['category_description'];
		$assign_fields['category_parent'] = intval($data['category_parent']);
		$assign_fields['category_featured'] = intval($data['category_featured']);
		if(isset($data['category_file']) && $data['category_file'] != ''){
			$assign_fields['category_file'] = $data['category_file'];
		}
		$assign_fields['category_meta_title'] = $data['category_meta_title'];
		$assign_fields['category_meta_keywords'] = $data['category_meta_keywords'];
		$assign_fields['category_meta_description'] = $data['category_meta_description'];
		$assign_fields['category_display_order'] = intval($data['category_display_order']);
		if($category_id === 0 && !isset($data['category_status'])){
			$assign_fields['category_status'] = 1;
		}if($category_id > 0 && isset($data['category_status'])){
			$assign_fields['category_status'] = intval($data['category_status']);
		}
		$record->assignValues($assign_fields);
		if($category_id === 0 && $record->addNew()){
			$this->category_id=$record->getId();
		}elseif($category_id > 0 && $record->update(array('smt'=>'category_id=?', 'vals'=>array($category_id)))){
			$this->category_id=$category_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		
		if (!$this->db->deleteRecords('tbl_url_alias', array('smt' => 'url_alias_query = ?', 'vals' => array('category_id='.$this->getCategoryId())))){
			$this->error = $this->db->getError();
			return false;
		}
	
		if (!empty($data['seo_url_keyword'])) {
			if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'category_id='.$this->getCategoryId(),'url_alias_keyword'=>$data['seo_url_keyword']))){
				$this->error = $this->db->getError();
				return false;
			}
		}
		
		
		if (!$this->db->deleteRecords('tbl_category_filter', array('smt' => 'category_id = ?', 'vals' => array($this->getCategoryId())))){
			$this->error = $this->db->getError();
			return false;
		}
		$record = new TableRecord('tbl_category_filter');
		$record->assignValues(array("category_id"=>$this->getCategoryId()));
		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $key=>$val){
				$record->assignValues(array("filter_id"=>$val));	
				if($val>0){ 
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		
		return $this->getCategoryId();
	}
	
	function updateCategoryStatus($category_id,$data_update=array()) {
		$category_id = intval($category_id);
		if($category_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_categories', $data_update, array('smt'=>'`category_id` = ?', 'vals'=> array($category_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($category_id){
		$category_id = intval($category_id);
		if($category_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_categories', array('category_is_deleted' => 1), array('smt' => 'category_id = ? ', 'vals' => array($category_id)))){
			$this->db->deleteRecords('tbl_url_alias', array('smt'=>'url_alias_query=? ', 'vals'=>array('category_id='.$category_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function checkParentCatgeory($parent_id=0, $type=1, $pagesize=100,$topParent_id=0) {
        $db = &Syspage::getdb(); 
		$srch = new SearchBase('tbl_categories', 'tc'); 
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tc.category_parent');
		$srch->addMultipleFields(array('tc.category_parent',"COUNT(tc.category_id) AS total_sub_cats"));
		$qry_subcats_received = $srch->getQuery();
		
        $query = "SELECT tc.*,IFNULL(tcs.total_sub_cats,0) as subcats FROM tbl_categories tc LEFT JOIN (".$qry_subcats_received.") tcs on tcs.category_parent=tc.category_id WHERE category_type='{$type}' and tc.category_parent='{$parent_id}'";
        $query .= ' order by category_display_order,category_name';
				 
        $rs = $db->query($query);
        $categories = $db->fetch_all($rs);
		$this->cat[] = $parent_id; 
        foreach ($categories as $catkey=>$catval) {  
			$this->cat[] = $catval['category_id']; 
            self::checkParentCatgeory($catval['category_id'], $type,0,0); 
        }
		
        return $this->cat;
    } 
	
	function getAssociativeArray($category_type=null) {
        $db = &Syspage::getdb();
		$query = "SELECT category_id, category_name FROM tbl_categories WHERE category_is_deleted='0' and category_status=1 ";
        if (!empty($category_type)) $query .= " AND category_type IN ({$category_type})";
		$query .= " ORDER BY category_name";
        $rs = $db->query($query);
        return $db->fetch_all_assoc($rs);
    }
	
	function getCategoriesAssocArray($parent_id=0, $not_to_include=false, $level=0,$cat_type=1) {
        $db = &Syspage::getdb();
        $query = "SELECT category_id, category_name FROM tbl_categories WHERE category_status=1 and category_is_deleted=0 and category_type=$cat_type and category_parent='{$parent_id}'";
        if (!empty($not_to_include)) $query .= " AND category_id NOT IN ({$not_to_include})";
        $query .= '  order by category_display_order';
        $rs = $db->query($query);
        $categories = $db->fetch_all_assoc($rs);
        $return = array();
        $category_name_prefix = '';
        for($i=0;$i<$level;$i++) {
            $category_name_prefix .= '-- ';
        }
        foreach ($categories as $category_id=>$category_name) {
            $return[$category_id] = $category_name_prefix . $category_name;
            $return += self::getCategoriesAssocArray($category_id, $not_to_include, $level+1);
        }
        return $return;
    }
	
	public function recordCategoryWeightage($category_id){
		$srObj=new SmartRecommendations();
		return $srObj->addUpdateUserBrowsingActivity($category_id,3);
	}
	
	function getProductTopCategoriesHavingRecords() {
		$all_cats=Categories::getCategories(array("parent"=>0,"status"=>1,"type"=>1));
		foreach($all_cats as $key=>$val):
			$criteria = array("category"=>$val["category_id"]);
			$pObj= new Products();
			$total_records = $pObj->getProductsCount($criteria);
			$val["category_products"]=$total_records;
			if ($total_records>0):
				$top_categories[]=$val;
			endif;
		endforeach;
		return $top_categories;
			/*$categories=Categories::getCategoriesAssocArrayFront($parent,1);
			foreach($categories[$parent] as $key=>$val):
				$criteria = array("shop"=>$shop,"category"=>$val["category_id"]);
				$pObj= new Products();
				$total_records = $pObj->getProductsCount($criteria);
				$val["category_products"]=$total_records;
				if ($total_records>0):
					$shop_categories[]=$val;
				endif;
			endforeach;
			$return = array();
			foreach ($shop_categories as $catkey=>$catval) {
					$category_id=$catval["category_id"];
        		    $return[$parent][$category_id] = $catval;
		            $return += self::getProductCategoriesHavingRecords($category_id, $shop);
       		}
			return $return;	*/
	}
	
}
