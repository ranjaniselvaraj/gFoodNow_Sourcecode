<?php
class Collections {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCollectionId() {
        return $this->collection_id;
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
	
	function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
        $rs = $this->db->query($sql);
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getCollections($criteria,$listing_only=false) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $arr_collections=$this->db->fetch_all($rs);
		if ($listing_only)
		return $arr_collections;
		
		foreach($arr_collections as $sn=>$val){
			switch($val['collection_type']) {
           		case 'C':
               		 $collection_categories=$this->getCollectionCategories($val["collection_id"],$criteria["front_end"]);
		             break;
				case 'P':
               		$collection_products=$this->getCollectionProducts($val["collection_id"],$criteria["front_end"]);
	                break;
				case 'S':
					$collection_shops=$this->getCollectionShops($val["collection_id"],$criteria["front_end"]);
				break;
            }
			$val["collection_categories"]=$collection_categories;
			$val["collection_products"]=$collection_products;
			$val["collection_shops"]=$collection_shops;
			
			$collections[] = $val;
		}
		return $collections;
    }
	
   
    
    function search($criteria) {
		$srch=new SearchBase('tbl_collections','tc');
		$srch->addCondition('tc.collection_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tc.collection_id', '=', intval($val));
                break;
			case 'status':
                $srch->addCondition('tc.collection_status', '=', intval($val));
                break;
			case 'type':
				if (is_array($val))
                	$srch->addCondition('tc.collection_type', 'IN', $val);
				else
					$srch->addCondition('tc.collection_type', '=', $val);	
       	        break;	
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
            }
        }
		$srch->addOrder('collection_status','desc');
		$srch->addOrder('collection_display_order','asc');
        return $srch;
    }
	
	function getCollectionCategories($id,$front_end=1) {
		$categories=Categories::getCatgeoryTreeStructure();
		$collection_category_data = array();
		$srch = new SearchBase('tbl_collection_categories', 'tcc');
		$srch->joinTable('tbl_categories', 'LEFT OUTER JOIN', 'tcc.dctc_category_id = tc.category_id', 'tc');
		$srch->joinTable('tbl_collections', 'LEFT OUTER JOIN', 'tcc.dctc_collection_id = tcol.collection_id', 'tcol');
		$srch->addCondition('tcc.dctc_collection_id', '=', intval($id));
		if ($front_end){
			$srch->addCondition('tc.category_status', '=', 1);
			$srch->addCondition('tc.category_is_deleted', '=', 0);
		}
		$srch->addOrder('dctc_display_order','asc');
		$rs = $srch->getResultSet();
		while ($row=$this->db->fetch($rs)){
			$sort_by = "price";
           	switch($row["collection_criteria"]) {
	            case '0':
    	            $sort_by="plth";
                break;
				case '1':
    	            $sort_by="phtl";
                break;
				case '2':
    	            $sort_by="best";
                break;
				case '3':
    	            $sort_by="rece";
                break;
				case '4':
    	            $sort_by="rate";
                break;
				case '5':
    	            $sort_by="feat";
                break;
            }
        
			$criteria=array("category"=>$row["category_id"],"status"=>1,"favorite"=>User::getLoggedUserId(),"pagesize"=>$row["collection_child_records"],"sort"=>$sort_by);
			$pObj= new Products();
			$pObj->joinWithDetailTable();
			$pObj->joinWithPromotionsTable();
			$pObj->addSpecialPrice();
			$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			
			$collection_category_products=$pObj->getProducts($criteria);
			$collection_category_data[] = array('id' => $row['category_id'],'name'=> $categories[$row["category_id"]],'short_name'=> $row["category_name"],"products"=>$collection_category_products);
			
		}
		return $collection_category_data;
	}
	
	function getCollectionProducts($id,$front_end=1) {
		$collection_product_data = array();
		$srch = new SearchBase('tbl_collection_products', 'tcp');
		$srch->joinTable('tbl_products', 'LEFT OUTER JOIN', 'tcp.dctc_product_id = tp.prod_id', 'tp');
		$srch->addCondition('tcp.dctc_collection_id', '=', intval($id));
		$srch->addOrder('dctc_display_order','asc');
		$rs = $srch->getResultSet();
		while ($row=$this->db->fetch($rs)){
			if (!$front_end){
				$collection_product_data[] = array('id' => $row['prod_id'],'name'=> $row["prod_name"]);
			}else{
				
				$pObj= new Products();
				$pObj->joinWithDetailTable();
				$pObj->joinWithPromotionsTable();
				$pObj->addSpecialPrice();
				$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			
				$product = $pObj->getData($row["prod_id"],array("status"=>1,"favorite"=>User::getLoggedUserId()));
				if ($product)
					$collection_product_data[] = $product;
			}
		}
		return $collection_product_data;
	}
	
	function getCollectionBrands($id,$front_end=1) {
		$collection_brand_data = array();
		$srch = new SearchBase('tbl_collection_brands', 'tcb');
		$srch->joinTable('tbl_product_brands', 'LEFT OUTER JOIN', 'tcb.dctc_brand_id = tpb.brand_id', 'tpb');
		$srch->addCondition('tcb.dctc_collection_id', '=', intval($id));
		if ($front_end){
			$srch->addCondition('tpb.brand_status', '=', 1);
			$srch->addCondition('tpb.brand_is_deleted', '=', 0);
		}
		$srch->addOrder('dctc_display_order','asc');
		$rs = $srch->getResultSet();
		while ($row=$this->db->fetch($rs)){
			$collection_brand_data[] = array('id' => $row['brand_id'],'name'=> $row["brand_name"]);
		}
		return $collection_brand_data;
	}
	
	function getCollectionShops($id,$front_end=1) {
		$collection_shop_data = array();
		$srch = new SearchBase('tbl_collection_shops', 'tcs');
		$srch->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'tcs.dctc_shop_id = ts.shop_id', 'ts');
		$srch->addCondition('tcs.dctc_collection_id', '=', intval($id));
		$srch->addOrder('dctc_display_order','asc');
		$rs = $srch->getResultSet();
		while ($row=$this->db->fetch($rs)){
			if (!$front_end){
				$collection_shop_data[] = array('id' => $row['shop_id'],'name'=> $row["shop_name"],'logo'=> $row["shop_logo"]);
			}else{
				$sObj=new Shops();
				$shop = $sObj->getData($row["shop_id"],array("status"=>1));
				if ($shop && $shop["totProducts"]>0){
					$product= new Products();
					$product->joinWithPromotionsTable();
					$product->addSpecialPrice();
					$product->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
					$product->setPagesize(4);
					$shop_products=$product->getProducts(array("shop"=>$shop["shop_id"]));
					$arr_shop_prods=array("products"=>$shop_products);
					$collection_shop_data[]=array_merge($shop,$arr_shop_prods);
				}
					//$collection_shop_data[] = array('id' => $shop['shop_id'],'name'=> $shop["shop_name"],'logo'=> $shop["shop_logo"]);
			}
		
			
		}
		return $collection_shop_data;
	}
	
	function addUpdate($data){
		$collection_id = intval($data['collection_id']);
		$record = new TableRecord('tbl_collections');
		$assign_fields = array();
		$assign_fields['collection_name'] = $data['collection_name'];
		$assign_fields['collection_display_title'] = $data['collection_display_title'];
		if(isset($data['collection_image']) && $data['collection_image'] != ''){
			$assign_fields['collection_image'] = $data['collection_image'];
		}
		$assign_fields['collection_display_order'] = intval($data['collection_display_order']);
		$assign_fields['collection_type'] = $data['collection_type'];
		$assign_fields['collection_criteria'] = intval($data['collection_criteria']);
		$assign_fields['collection_primary_records'] = intval($data['collection_primary_records']);
		$assign_fields['collection_child_records'] = intval($data['collection_child_records']);
		if($collection_id === 0 && !isset($data['collection_status'])){
			$assign_fields['collection_status'] = 1;
		}if($collection_id > 0 && isset($data['collection_status'])){
			$assign_fields['collection_status'] = intval($data['collection_status']);
		}
		$record->assignValues($assign_fields);
		if($collection_id === 0 && $record->addNew()){
			$this->collection_id=$record->getId();
		}elseif($collection_id > 0 && $record->update(array('smt'=>'collection_id=?', 'vals'=>array($collection_id)))){
			$this->collection_id=$collection_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
   if (!$this->db->deleteRecords('tbl_collection_categories', array('smt' => 'dctc_collection_id = ?', 'vals' => array($this->getCollectionId())))){
		$this->error = $this->db->getError();
		return false;
	}
	$record = new TableRecord('tbl_collection_categories');
	$record->assignValues(array("dctc_collection_id"=>$this->getCollectionId()));
	if (isset($data['categories'])) {
		foreach ($data['categories'] as $key=>$val){ $catInc++;
			$record->assignValues(array("dctc_category_id"=>$val,"dctc_display_order"=>$catInc));	
			if($val>0){ 
				if (!$record->addNew()){
					$this->error = $this->db->getError();
					return false;
				}
			}
		}
	}
		
	if (!$this->db->deleteRecords('tbl_collection_products', array('smt' => 'dctc_collection_id = ?', 'vals' => array($this->getCollectionId())))){
			$this->error = $this->db->getError();
			return false;
		}
		$record = new TableRecord('tbl_collection_products');
		$record->assignValues(array("dctc_collection_id"=>$this->getCollectionId()));
		if (isset($data['products'])) {
			foreach ($data['products'] as $key=>$val){ $prodInc++;
				$record->assignValues(array("dctc_product_id"=>$val,"dctc_display_order"=>$prodInc));	
				if($val>0){ 
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
	if (!$this->db->deleteRecords('tbl_collection_brands', array('smt' => 'dctc_collection_id = ?', 'vals' => array($this->getCollectionId())))){
			$this->error = $this->db->getError();
			return false;
		}
		$record = new TableRecord('tbl_collection_brands');
		$record->assignValues(array("dctc_collection_id"=>$this->getCollectionId()));
		if (isset($data['brands'])) {
			foreach ($data['brands'] as $key=>$val){ $brandInc++;
				$record->assignValues(array("dctc_brand_id"=>$val,"dctc_display_order"=>$brandInc));	
				if($val>0){ 
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}	
	
	if (!$this->db->deleteRecords('tbl_collection_shops', array('smt' => 'dctc_collection_id = ?', 'vals' => array($this->getCollectionId())))){
			$this->error = $this->db->getError();
			return false;
		}
		$record = new TableRecord('tbl_collection_shops');
		$record->assignValues(array("dctc_collection_id"=>$this->getCollectionId()));
		if (isset($data['shops'])) {
			foreach ($data['shops'] as $key=>$val){ $shopInc++;
				$record->assignValues(array("dctc_shop_id"=>$val,"dctc_display_order"=>$shopInc));	
				if($val>0){ 
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}		
		
		
		return $this->collection_id;
	}
	
	function updateCollectionStatus($collection_id,$data_update=array()) {
		$collection_id = intval($collection_id);
		if($collection_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_collections', $data_update, array('smt'=>'`collection_id` = ?', 'vals'=> array($collection_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($collection_id){
		$collection_id = intval($collection_id);
		if($collection_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_collections', array('collection_is_deleted' => 1), array('smt' => 'collection_id = ?', 'vals' => array($collection_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}