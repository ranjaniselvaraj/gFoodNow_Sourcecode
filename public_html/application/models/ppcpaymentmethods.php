<?php
class PPCPaymentmethods extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	public function getPaymentMethodFields($payment_method_code){
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
		$srch->addOrder('ppcpmethod_status','desc');
		$srch->addOrder('ppcpmethod_display_order','ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
   }
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_ppc_payment_methods', 'tpm');
        foreach($criteria as $key=>$val) {
			//if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tpm.ppcpmethod_id', '=', intval($val));
                break;
			case 'code':
                $srch->addCondition('tpm.ppcpmethod_code', '=', $val);
                break;	
			case 'name':
                $srch->addCondition('tpm.ppcpmethod_name', '=', $val);
                break;	
			case 'status':
	            $srch->addCondition('tpm.ppcpmethod_status', '=', intval($val));
                break;	
            }
        }
        return $srch;
    }
	
	
	function addUpdate($data){
		$ppcpmethod_id = intval($data['ppcpmethod_id']);
		$record = new TableRecord('tbl_ppc_payment_methods');
		$assign_fields = array();
		$assign_fields['ppcpmethod_name'] = $data['ppcpmethod_name'];
		$assign_fields['ppcpmethod_details'] = $data['ppcpmethod_details'];
		$assign_fields['ppcpmethod_display_order'] = intval($data['ppcpmethod_display_order']);
		if(isset($data['ppcpmethod_icon']) && $data['ppcpmethod_icon'] != ''){
			$assign_fields['ppcpmethod_icon'] = $data['ppcpmethod_icon'];
		}
		if($ppcpmethod_id === 0 && !isset($data['ppcpmethod_status'])){
			$assign_fields['ppcpmethod_status'] = 1;
		}if($ppcpmethod_id > 0 && isset($data['ppcpmethod_status'])){
			$assign_fields['ppcpmethod_status'] = intval($data['ppcpmethod_status']);
		}
		$record->assignValues($assign_fields);
		if($ppcpmethod_id > 0 && $record->update(array('smt'=>'ppcpmethod_id=?', 'vals'=>array($ppcpmethod_id)))){
			$this->ppcpmethod_id=$ppcpmethod_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->ppcpmethod_id;
	}
	
	function updatePaymentMethodStatus($ppcpmethod_id,$data_update=array()) {
		$ppcpmethod_id = intval($ppcpmethod_id);
		if($ppcpmethod_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_ppc_payment_methods', $data_update, array('smt'=>'`ppcpmethod_id` = ?', 'vals'=> array($ppcpmethod_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
    
	
   
}