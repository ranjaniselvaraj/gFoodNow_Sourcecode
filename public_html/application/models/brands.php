<?php
class Brands extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getBrands($criteria,$pagesize) {
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
		$pObj=new Products();
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
    
	
	public function recordBrandWeightage($brand_id){
		$srObj=new SmartRecommendations();
		return $srObj->addUpdateUserBrowsingActivity($brand_id,2);
	}
	
   
}