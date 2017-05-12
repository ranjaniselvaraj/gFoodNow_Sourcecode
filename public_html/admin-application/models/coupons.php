<?php
class Coupons extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCouponId() {
        return $this->coupon_id;
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
	
    function getData($id) {
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
	
	function getCouponByCode($val) {
        $srch = new SearchBase('tbl_coupons', 'tc');
		$srch->addCondition('tc.coupon_code', '=',$val);
		$srch->addCondition('tc.coupon_is_deleted', '=',0);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_coupons', 'tc');
		$srch->addCondition('tc.coupon_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tc.coupon_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tc.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tc.coupon_id', '=', intval($val));
                break;
			case 'keyword':
                if ($val!=""){
					$srch->addDirectCondition('(tc.coupon_title LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tc.coupon_code like '. $this->db->quoteVariable('%' . $val . '%') .')');
				}	
                break;
			case 'pagesize':
					$srch->setPageSize($val);
					break;
			case 'page':
					$srch->setPageNumber($val);
					break;		
            
            }
        }
        return $srch;
    }
	
	function getCoupons($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('coupon_status','desc');
		$srch->addOrder('coupon_id','desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	function getCouponCategories($id) {
		$coupon_category_data = array();
		$categories=Categories::getCatgeoryTreeStructure();
		$rs = $this->db->query("SELECT * FROM tbl_coupon_categories t1 INNER JOIN tbl_categories t2 on t1.dctc_category_id=t2.category_id  WHERE dctc_coupon_id = '" . (int)$id . "' order by category_display_order asc");
		while ($row=$this->db->fetch($rs)){
			$coupon_category_data[] = array('id' => $row['category_id'],'name'=> strip_tags(html_entity_decode($categories[$row["category_id"]], ENT_QUOTES, 'UTF-8')));
		}
		return $coupon_category_data;
	}
	
	function getCouponProducts($id) {
		$coupon_product_data = array();
		$rs = $this->db->query("SELECT * FROM tbl_coupon_products t1 INNER JOIN tbl_products t2 on t1.dctc_product_id=t2.prod_id  WHERE dctc_coupon_id = '" . (int)$id . "' order by prod_name asc");
		while ($row=$this->db->fetch($rs)){
			$coupon_product_data[] = array('id' => $row['prod_id'],'name'=> strip_tags(html_entity_decode($row["prod_name"], ENT_QUOTES, 'UTF-8')));
		}
		return $coupon_product_data;
	}
	
	function getCouponHistories($coupon_id) {
		$coupon_product_data = array();
		$rs = $this->db->query("SELECT tch.order_id, tu.user_name AS customer, tch.amount, tch.date_added FROM tbl_coupons_history tch LEFT JOIN tbl_users tu ON (tch.customer_id = tu.user_id) WHERE tch.coupon_id = '" . (int)$coupon_id . "' ORDER BY tch.date_added ASC");
		return $row=$this->db->fetch_all($rs);
	}
	
	function addUpdate($data){
		$coupon_id = intval($data['coupon_id']);
		$record = new TableRecord('tbl_coupons');
		$assign_fields = array();
		$assign_fields['coupon_title'] = $data['coupon_title'];
		$assign_fields['coupon_description'] = $data['coupon_description'];
		$assign_fields['coupon_code'] = $data['coupon_code'];
		if(isset($data['coupon_file']) && $data['coupon_file'] != ''){
			$assign_fields['coupon_image'] = $data['coupon_file'];
		}
		$assign_fields['coupon_min_order_value'] = $data['coupon_min_order_value'];
		$assign_fields['coupon_discount_type'] = $data['coupon_discount_type'];
		$assign_fields['coupon_discount_value'] = $data['coupon_discount_value'];
		$assign_fields['coupon_max_discount_value'] = $data['coupon_max_discount_value'];
		$assign_fields['coupon_start_date'] = $data['coupon_start_date'];
		$assign_fields['coupon_end_date'] = $data['coupon_end_date'];
		$assign_fields['coupon_uses'] = intval($data['coupon_uses']);
		$assign_fields['coupon_uses_customer'] = intval($data['coupon_uses_customer']);
		//$assign_fields['coupon_free_shipping'] = intval($data['coupon_free_shipping']);
		if($coupon_id === 0 && !isset($data['coupon_status'])){
			$assign_fields['coupon_status'] = 1;
		}if($coupon_id > 0 && isset($data['coupon_status'])){
			$assign_fields['coupon_status'] = intval($data['coupon_status']);
		}
		$record->assignValues($assign_fields);
		if($coupon_id === 0 && $record->addNew()){
			$this->coupon_id=$record->getId();
		}elseif($coupon_id > 0 && $record->update(array('smt'=>'coupon_id=?', 'vals'=>array($coupon_id)))){
			$this->coupon_id=$coupon_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
   if (!$this->db->deleteRecords('tbl_coupon_categories', array('smt' => 'dctc_coupon_id = ?', 'vals' => array($this->getCouponId())))){
		$this->error = $this->db->getError();
		throw new Exception('');
	}
	$record = new TableRecord('tbl_coupon_categories');
	$record->assignValues(array("dctc_coupon_id"=>$this->getCouponId()));
	if (isset($data['categories'])) {
		foreach ($data['categories'] as $key=>$val){ 
			$record->assignValues(array("dctc_category_id"=>$val));	
			if($val>0){ 
				if (!$record->addNew()){
					$this->error = $this->db->getError();
					return false;
				}
			}
		}
	}
		
	if (!$this->db->deleteRecords('tbl_coupon_products', array('smt' => 'dctc_coupon_id = ?', 'vals' => array($this->getCouponId())))){
			$this->error = $this->db->getError();
			throw new Exception('');
		}
		$record = new TableRecord('tbl_coupon_products');
		$record->assignValues(array("dctc_coupon_id"=>$this->getCouponId()));
		if (isset($data['products'])) {
			foreach ($data['products'] as $key=>$val){ 
				$record->assignValues(array("dctc_product_id"=>$val));	
				if($val>0){ 
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		return $this->coupon_id;
	}
	
	function updateCouponStatus($coupon_id,$data_update=array()) {
		$coupon_id = intval($coupon_id);
		if($coupon_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_coupons', $data_update, array('smt'=>'`coupon_id` = ?', 'vals'=> array($coupon_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($coupon_id){
		$coupon_id = intval($coupon_id);
		if($coupon_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_coupons', array('coupon_is_deleted' => 1), array('smt' => 'coupon_id = ?', 'vals' => array($coupon_id)))){
			return true;
		}
		
		$this->error = $this->db->getError();
		return false;
	}
	
    
   
}