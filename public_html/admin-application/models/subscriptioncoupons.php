<?php
class Subscriptioncoupons extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCouponId() {
        return $this->subscoupon_id;
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
        $srch = new SearchBase('tbl_subscription_coupons', 'tc');
		$srch->addCondition('tc.subscoupon_code', '=',$val);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_subscription_coupons', 'tc');
		$srch->joinTable('tbl_subscription_merchant_sub_packages','INNER JOIN','merchantsubpack_id = subscoupon_merchantsubpack');
		$srch->joinTable('tbl_subscription_merchant_packages','INNER JOIN','merchantpack_id = merchantsubpack_merchantpack_id');
		$srch->addCondition('tc.subscoupon_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tc.subscoupon_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tc.*','merchantpack_id as package'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tc.subscoupon_id', '=', intval($val));
                break;
			case 'keyword':
                if ($val!=""){
					$srch->addDirectCondition('(tc.subscoupon_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tc.subscoupon_code like '. $this->db->quoteVariable('%' . $val . '%') .')');
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
	
	public function getCoupon($code) {
		$status = true;
		$cartObj=new Cart($this->logged_user_id);
		$cart_sub_total=$cartObj->getSubTotal();
		$cartSubscription = $this->Cart->getSubscription();
		$row_coupon_query = $this->db->query("SELECT * FROM `tbl_subscription_coupons` WHERE subscoupon_code = " . $this->db->quoteVariable($code) . " AND ((subscoupon_start_date = '0000-00-00' OR subscoupon_start_date <= CURRENT_DATE()) AND (subscoupon_end_date = '0000-00-00' OR subscoupon_end_date >= CURRENT_DATE())) AND subscoupon_active = '1' and subscoupon_is_deleted=0 and subscoupon_merchantsubpack = '".$cartSubscription['merchantsubpack_id']."'");
		$coupon_query = $this->db->fetch($row_coupon_query);
		if ($coupon_query){
			
			$row_coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_subscription_coupons_history ch WHERE ch.subscouponhistory_subscoupon_id = '" . (int)$coupon_query['subscoupon_id'] . "'");
			$coupon_history_query = $this->db->fetch($row_coupon_history_query);
			if ($coupon_query['subscoupon_uses_per_coupon'] > 0 && ($coupon_history_query['total'] >= $coupon_query['subscoupon_uses_per_coupon'])) {
				$status = false;
			}
			if (User::getLoggedUserId()) {
				$customer_id=User::getLoggedUserId();
				$row_coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_subscription_coupons_history ch WHERE ch.subscouponhistory_subscoupon_id = '" . (int)$coupon_query['subscoupon_id'] . "' AND ch.subscouponhistory_customer_id = '" . (int)$customer_id . "'");
				$coupon_history_query = $this->db->fetch($row_coupon_history_query);
				if ($coupon_query['subscoupon_uses_per_customer'] > 0 && ($coupon_history_query['total'] >= $coupon_query['subscoupon_uses_per_customer'])) {
					$status = false;
				}
			}
			
			$product_data = array();
			$product_data[] =$cartSubscription['merchantsubpack_id'];
			
			
		}else {
			$status = false;
		}
		if ($status)
			return array_merge($coupon_query,array("products"=>$product_data));
		
		//return $coupon_query;
		
	}
	
	
	function getCoupons($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('subscoupon_active','desc');
		$srch->addOrder('subscoupon_id','desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	
	
	function getCouponHistories($subscoupon_id) {
		$rs = $this->db->query("SELECT tch.subscouponhistory_order_id, tu.user_name AS customer, tch.subscouponhistory_amount, tch.subscouponhistory_date_added FROM tbl_subscription_coupons_history tch LEFT JOIN tbl_users tu ON (tch.subscouponhistory_customer_id = tu.user_id) WHERE tch.subscouponhistory_subscoupon_id = '" . intval($subscoupon_id) . "' ORDER BY tch.subscouponhistory_date_added ASC");
		return $row=$this->db->fetch_all($rs);
	}
	
	function addUpdate($data){
		$subscoupon_id = intval($data['subscoupon_id']);
		$record = new TableRecord('tbl_subscription_coupons');
		$assign_fields = array();
		$assign_fields['subscoupon_name'] = $data['subscoupon_name'];
		$assign_fields['subscoupon_description'] = $data['subscoupon_description'];
		$assign_fields['subscoupon_code'] = $data['subscoupon_code'];
		$assign_fields['subscoupon_merchantsubpack'] = $data['subscoupon_merchantsubpack'];
		$assign_fields['subscoupon_discount_type'] = $data['subscoupon_discount_type'];
		$assign_fields['subscoupon_discount_value'] = $data['subscoupon_discount_value'];
		$assign_fields['subscoupon_max_discount_value'] = $data['subscoupon_max_discount_value'];
		$assign_fields['subscoupon_start_date'] = $data['subscoupon_start_date'];
		$assign_fields['subscoupon_end_date'] = $data['subscoupon_end_date'];
		$assign_fields['subscoupon_discount_valid_for'] = $data['subscoupon_discount_valid_for'];
		$assign_fields['subscoupon_uses_per_coupon'] = intval($data['subscoupon_uses_per_coupon']);
		$assign_fields['subscoupon_uses_per_customer'] = intval($data['subscoupon_uses_per_customer']);
		
		if($subscoupon_id === 0 && !isset($data['subscoupon_active'])){
			$assign_fields['subscoupon_active'] = 1;
		}if($subscoupon_id > 0 && isset($data['subscoupon_active'])){
			$assign_fields['subscoupon_active'] = intval($data['subscoupon_active']);
		}
		$record->assignValues($assign_fields);
		if($subscoupon_id === 0 && $record->addNew()){
			$this->subscoupon_id=$record->getId();
		}elseif($subscoupon_id > 0 && $record->update(array('smt'=>'subscoupon_id=?', 'vals'=>array($subscoupon_id)))){
			$this->subscoupon_id=$subscoupon_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		return $this->subscoupon_id;
	}
	
	function updateCouponStatus($subscoupon_id,$data_update=array()) {
		$subscoupon_id = intval($subscoupon_id);
		if($subscoupon_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_subscription_coupons', $data_update, array('smt'=>'`subscoupon_id` = ?', 'vals'=> array($subscoupon_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($subscoupon_id){
		$subscoupon_id = intval($subscoupon_id);
		if($subscoupon_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_subscription_coupons', array('subscoupon_is_deleted' => 1), array('smt' => 'subscoupon_id = ?', 'vals' => array($subscoupon_id)))){
			return true;
		}
		
		$this->error = $this->db->getError();
		return false;
	}
	
    
   
}