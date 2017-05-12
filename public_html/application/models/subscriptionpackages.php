<?php
class SubscriptionPackages extends Model{
	
    function __construct(){
		$this->db = Syspage::getdb();
    }
	function getPackageId() {
        return $this->merchantpack_id;
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
	
	function getData( $id, $criteria=array() ) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
		$add_criteria = array_merge($add_criteria,$criteria);
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getSubscriptionPackages($criteria=array()) {
		$add_criteria = array();
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return array();
		}
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
		
	function search($criteria, $count='') {
        $srch = new SearchBase('tbl_subscription_merchant_packages', 'tmp');
		$srch->joinTable('tbl_subscription_merchant_sub_packages','LEFT OUTER JOIN','tmp.merchantpack_id = tmsp.merchantsubpack_merchantpack_id','tmsp');
		
        if($count==true) {
            $srch->addFld('COUNT(tmp.merchantpack_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tmp.*'));
        }
		$srch->addFld('COUNT(merchantsubpack_id) as tot_sub_packages ');
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tmp.merchantpack_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tmp.merchantpack_name', 'like', '%'.$val.'%');
                break;
			case 'status':
				$srch->addCondition('tmp.merchantpack_active', '=', $val);
			break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
            }
        }
		$srch->addOrder('tmp.merchantpack_active', 'DESC');
		$srch->addOrder('tmp.merchantpack_display_order', 'asc');
        // $srch->addOrder('tmp.merchantpack_name', 'asc');
        $srch->addOrder('tmp.merchantpack_id', 'asc');
		$srch->addGroupBy('merchantpack_id');
        return $srch;
    }
	
	function addUpdate($data){
		$merchantpack_id = intval($data['merchantpack_id']);
		$record = new TableRecord('tbl_subscription_merchant_packages');
		$assign_fields = array();
		$assign_fields['merchantpack_name'] = $data['merchantpack_name'];
		$assign_fields['merchantpack_description'] = $data['merchantpack_description'];
		$assign_fields['merchantpack_commission_rate'] = $data['merchantpack_commission_rate'];
		$assign_fields['merchantpack_max_products'] = $data['merchantpack_max_products'];
		$assign_fields['merchantpack_display_order'] = $data['merchantpack_display_order'];
		$assign_fields['merchantpack_images_per_product'] = $data['merchantpack_images_per_product'];
		$assign_fields['merchantpack_free_trial_days'] = $data['merchantpack_free_trial_days'];
		$record->assignValues($assign_fields);
		if($merchantpack_id === 0 && $record->addNew()){
			$this->merchantpack_id=$record->getId();
		}elseif($merchantpack_id > 0 && $record->update(array('smt'=>'merchantpack_id=?', 'vals'=>array($merchantpack_id)))){
			$this->merchantpack_id=$merchantpack_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->merchantpack_id;
	}
	
	function getAssociativeArray($criteria = array()){
		$srch = new SearchBase( 'tbl_subscription_merchant_packages', 'mp' );
		
		 foreach($criteria as $key=>$val) {
            switch($key) {
            case 'status':
                $srch->addCondition('mp.merchantpack_active', '=', $val);
                break;
            }
        }
		$srch->addMultipleFields(array('merchantpack_id', 'merchantpack_name'));
		$srch->addOrder('merchantpack_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function updatePackageStatus($merchantpack_id,$data_update=array()){
		$merchantpack_id = intval($merchantpack_id);
		if($merchantpack_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_subscription_merchant_packages', $data_update, array('smt'=>'`merchantpack_id` = ?', 'vals'=> array($merchantpack_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	
	}
	
	function getPackageOrderStatusAssoc(){
		$srch = new SearchBase('tbl_subscription_order_status', 'tsos');
		$srch->addCondition('sorder_status_is_deleted','=','0');
		$srch->addMultipleFields(array('sorder_status_id', 'sorder_status_name'));
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
}
