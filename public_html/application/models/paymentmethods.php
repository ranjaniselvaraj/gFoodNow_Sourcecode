<?php
class Paymentmethods extends Model {
	
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
		$srch->addOrder('pmethod_status','desc');
		$srch->addOrder('pmethod_display_order','ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
   }
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_payment_methods', 'tpm');
        foreach($criteria as $key=>$val) {
			//if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tpm.pmethod_id', '=', intval($val));
                break;
			case 'code':
                $srch->addCondition('tpm.pmethod_code', '=', $val);
                break;	
			case 'name':
                $srch->addCondition('tpm.pmethod_name', '=', $val);
                break;	
			case 'status':
	            $srch->addCondition('tpm.pmethod_status', '=', intval($val));
                break;	
            }
        }
        return $srch;
    }
	
	
	function addUpdate($data){
		$pmethod_id = intval($data['pmethod_id']);
		$record = new TableRecord('tbl_payment_methods');
		$assign_fields = array();
		$assign_fields['pmethod_name'] = $data['pmethod_name'];
		$assign_fields['pmethod_details'] = $data['pmethod_details'];
		$assign_fields['pmethod_display_order'] = intval($data['pmethod_display_order']);
		if(isset($data['pmethod_icon']) && $data['pmethod_icon'] != ''){
			$assign_fields['pmethod_icon'] = $data['pmethod_icon'];
		}
		if($pmethod_id === 0 && !isset($data['pmethod_status'])){
			$assign_fields['pmethod_status'] = 1;
		}if($pmethod_id > 0 && isset($data['pmethod_status'])){
			$assign_fields['pmethod_status'] = intval($data['pmethod_status']);
		}
		$record->assignValues($assign_fields);
		if($pmethod_id > 0 && $record->update(array('smt'=>'pmethod_id=?', 'vals'=>array($pmethod_id)))){
			$this->pmethod_id=$pmethod_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->pmethod_id;
	}
	
	function updatePaymentMethodStatus($pmethod_id,$data_update=array()) {
		$pmethod_id = intval($pmethod_id);
		if($pmethod_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_payment_methods', $data_update, array('smt'=>'`pmethod_id` = ?', 'vals'=> array($pmethod_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	static function writeLog($message=''){
		$logs_directory = CONF_INSTALLATION_PATH.'user-uploads/payment-logs';
		
		if (!file_exists($logs_directory)) {
			@mkdir($logs_directory, 0777, true);
		}
		
		$filename = $logs_directory.'/payment-logs2.txt'; //used to store payment logs
		$handle = fopen($filename, 'a+');
		fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true)  . "\n\n");
		fclose($handle); 
	}	
	
	
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT pmethod_id, pmethod_name FROM tbl_payment_methods";
		$query .= " ORDER BY pmethod_name";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function getPaymentMethodByCode($code) {
		$srch = new SearchBase('tbl_payment_methods', 'tpm');
		$srch->addCondition('tpm.pmethod_code', '=', $code);
		$rs = $srch->getResultSet();
		$payment_method=$this->db->fetch($rs);
		return $payment_method;
	}
    
	
   
}