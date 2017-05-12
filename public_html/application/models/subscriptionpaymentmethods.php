<?php
class SubscriptionPaymentmethods extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	public function getPaymentMethodFields($payment_method_code=''){
			if (empty($payment_method_code)) return array();
			return $this->getData('',array("code"=>$payment_method_code));
	}
	
    function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if (($id>0!=true) && count($add_criteria)<1) return array();
		if ($id>0) $add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        return $row;
	}
	
	function getPaymentMethods($criteria=array()) {
		$add_criteria = array(); 
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('subscriptionpmethod_status','desc');
		$srch->addOrder('subscriptionpmethod_display_order','ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
   }
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_subscription_payment_methods', 'tpm');
        foreach($criteria as $key=>$val) {
			//if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tpm.subscriptionpmethod_id', '=', intval($val));
                break;
			case 'code':
                $srch->addCondition('tpm.subscriptionpmethod_code', '=', $val);
                break;	
			case 'name':
                $srch->addCondition('tpm.subscriptionpmethod_name', '=', $val);
                break;	
			case 'status':
	            $srch->addCondition('tpm.subscriptionpmethod_status', '=', intval($val));
                break;	
            }
        }
        return $srch;
    }
	
	
	function addUpdate($data){
		$subscriptionpmethod_id = intval($data['subscriptionpmethod_id']);
		$record = new TableRecord('tbl_subscription_payment_methods');
		$assign_fields = array();
		$assign_fields['subscriptionpmethod_name'] = $data['subscriptionpmethod_name'];
		$assign_fields['subscriptionpmethod_details'] = $data['subscriptionpmethod_details'];
		$assign_fields['subscriptionpmethod_display_order'] = intval($data['subscriptionpmethod_display_order']);
		if(isset($data['subscriptionpmethod_icon']) && $data['subscriptionpmethod_icon'] != ''){
			$assign_fields['subscriptionpmethod_icon'] = $data['subscriptionpmethod_icon'];
		}
		if($subscriptionpmethod_id === 0 && !isset($data['subscriptionpmethod_status'])){
			$assign_fields['subscriptionpmethod_status'] = 1;
		}if($subscriptionpmethod_id > 0 && isset($data['subscriptionpmethod_status'])){
			$assign_fields['subscriptionpmethod_status'] = intval($data['subscriptionpmethod_status']);
		}
		$record->assignValues($assign_fields);
		if($subscriptionpmethod_id > 0 && $record->update(array('smt'=>'subscriptionpmethod_id=?', 'vals'=>array($subscriptionpmethod_id)))){
			$this->subscriptionpmethod_id=$subscriptionpmethod_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->subscriptionpmethod_id;
	}
	
	function updatePaymentMethodStatus($subscriptionpmethod_id,$data_update=array()) {
		$subscriptionpmethod_id = intval($subscriptionpmethod_id);
		if($subscriptionpmethod_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_subscription_payment_methods', $data_update, array('smt'=>'`subscriptionpmethod_id` = ?', 'vals'=> array($subscriptionpmethod_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
    
	
   
}