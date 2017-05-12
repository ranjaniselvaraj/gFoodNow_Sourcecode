<?php
class Brand extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getBrands($criteria,$pagesize=10) {
        $srch = self::search($criteria);
        if ((intval($pagesize)>0) || isset($criteria["pagesize"])){
			$srch->setPageSize(isset($criteria["pagesize"])?$criteria["pagesize"]:$pagesize);
		}else{
			$srch->doNotLimitRecords(true);
		}
		$srch->addOrder('brand_status','DESC');
		$srch->addOrder('brand_name','ASC');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
        $row = (($pagesize==1) || ($criteria["pagesize"]==1))?$this->db->fetch($rs):$this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getAssociativeArray(){
		$srch = new SearchBase('tbl_product_brands', 'b');
		$srch->addCondition('brand_is_deleted', '=',0);
		$srch->addCondition('brand_status', '=',1);
		$srch->addMultipleFields(array('brand_id', 'brand_name'));
		$srch->addOrder('brand_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
    function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
		$srch = self::search($add_criteria);
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tpb.brand_id = REPLACE(tua.url_alias_query,"brands_id=","")', 'tua');
		$srch->addCondition('tpb.brand_is_deleted', '=', 0);
        $srch->addMultipleFields(array('DISTINCT tpb.*','tua.url_alias_keyword as seo_url_keyword'));
		$srch->addCondition('tpb.brand_id', '=', $id);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
		$pObj=new Product();
		$pObj->addMultipleFields(array('tp.prod_brand as product_brand',"count(prod_id) as totBrandProducts"));
		$pObj->addGroupBy('tp.prod_brand');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		
		
        $srch = new SearchBase('tbl_product_brands', 'tpb');
		$srch->joinTable('(' . $pObj->getQuery() . ')', 'LEFT OUTER JOIN', 'tpb.brand_id = tb.product_brand', 'tb');
		$srch->addCondition('tpb.brand_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tpb.brand_id) AS total_rows');
        } else {
			$srch->addMultipleFields(array('DISTINCT tpb.*','COALESCE(tb.totBrandProducts,0) as totBrandProducts'));
        }
		//die($srch->getquery());
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tpb.brand_id', '=', intval($val));
                break;
			case 'name':
                $srch->addCondition('tpb.brand_name', '=', $val);
                break;	
			case 'status':
				if ($val!="")
	            $srch->addCondition('tpb.brand_status', '=', intval($val));
                break;	
			case 'keyword':
                $srch->addCondition('tpb.brand_name', 'like', '%'.$val.'%');
                break;
			case 'start':
                $srch->addCondition('tpb.brand_name', 'like', $val.'%');
                break;
			case 'must_products':
                $srch->addCondition('totBrandProducts', '>', 0);
                break;	
			case 'page':
				$srch->setPageNumber($val);
			break;	
			case 'pagesize':
				$srch->setPageSize($val);
			break;
			case 'order':
				$srch->addOrder($val);
			break;			
           
            }
        }
        //$srch->addOrder('t1.brand_name', 'asc');
        return $srch;
    }
    
	function addUpdate($data){
		$brand_id = intval($data['brand_id']);
		$record = new TableRecord('tbl_product_brands');
		$assign_fields = array();
		$assign_fields['brand_name'] = $data['brand_name'];
		$assign_fields['brand_description'] = $data['brand_description'];
		$assign_fields['brand_meta_title'] = $data['brand_meta_title'];
		$assign_fields['brand_meta_keywords'] = $data['brand_meta_keywords'];
		$assign_fields['brand_meta_description'] = $data['brand_meta_description'];
		if($brand_id === 0 && !isset($data['brand_status'])){
			$assign_fields['brand_status'] = 1;
		}if($brand_id > 0 && isset($data['brand_status'])){
			$assign_fields['brand_status'] = intval($data['brand_status']);
		}
		$record->assignValues($assign_fields);
		if($brand_id === 0 && $record->addNew()){
			$this->brand_id=$record->getId();
		}elseif($brand_id > 0 && $record->update(array('smt'=>'brand_id=?', 'vals'=>array($brand_id)))){
			$this->brand_id=$brand_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		if (!$this->db->deleteRecords('tbl_url_alias', array('smt' => 'url_alias_query = ?', 'vals' => array('brands_id='.$this->brand_id)))){
			$this->error = $this->db->getError();
			return false;
		}
	
		if (!empty($data['seo_url_keyword'])) {
			if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'brands_id='.$this->brand_id,'url_alias_keyword'=>$data['seo_url_keyword']))){
				$this->error = $this->db->getError();
				return false;
			}
		}
		return $this->brand_id;
	}
	
	function updateBrandStatus($brand_id,$data_update=array()) {
		$brand_id = intval($brand_id);
		if($brand_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_product_brands', $data_update, array('smt'=>'`brand_id` = ?', 'vals'=> array($brand_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($brand_id){
		$brand_id = intval($brand_id);
		if($brand_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_product_brands', array('brand_is_deleted' => 1), array('smt' => 'brand_id = ? ', 'vals' => array($brand_id)))){
			$this->db->deleteRecords('tbl_url_alias', array('smt'=>'url_alias_query=? ', 'vals'=>array('brands_id='.$brand_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	public function recordBrandWeightage($brand_id){
		$srObj=new SmartRecommendations();
		return $srObj->addUpdateUserBrowsingActivity($brand_id,2);
	}
	
   
}