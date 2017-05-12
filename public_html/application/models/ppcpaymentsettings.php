<?php
class PPCPaymentsettings extends Model {
	
	function __construct($payment_method_key){
		$this->db = Syspage::getdb();
		$this->payment_method_key=$payment_method_key;
    }
	
	function getError() {
        return $this->error;
    }
	
	public function getPaymentSettings(){
		if (!isset($this->payment_method_key)){
			$this->error="Error: Please create an object with Payment Method Key.";
			return false;
		}
		$payment_method=$this->getPaymentMethodByCode($this->payment_method_key);
		if (!$payment_method){
			$this->error = "Error: Payment method with this payment key does not exist.";
			return false;
		}
		$payment_method_settings = $this->getPaymentMethodFieldsById($payment_method["pmethod_id"]);
		$payment_settings=array();
		foreach($payment_method_settings as $pkey=>$pval){
			$payment_settings[$pval["ppcpmf_key"]]=$pval["ppcpmf_value"];
		}
		return array_merge($payment_settings,$payment_method);
	}
	
	function getPaymentMethodFieldsById($pmethod_id) {
		$srch = new SearchBase('tbl_ppc_payment_method_fields', 'tpmf');
		$srch->addCondition('tpmf.ppcpmf_pmethod_id', '=', (int)$pmethod_id);
		$srch->addOrder('ppcpmf_id','ASC');
		$rs = $srch->getResultSet();
		$payment_method_settings=$this->db->fetch_all($rs);
		return $payment_method_settings;
	}
	
		
	function getPaymentMethodByCode($code) {
		$srch = new SearchBase('tbl_payment_methods', 'tpm');
		$srch->addCondition('tpm.pmethod_code', '=', $code);
		$rs = $srch->getResultSet();
		$payment_method=$this->db->fetch($rs);
		return $payment_method;
	}
	 function saveSetting($arr_payment_settings){
			$payment_method=$this->getPaymentMethodByCode($this->payment_method_key);
			if (!$payment_method){
				$this->error = "Error: Payment method with defined payment key does not exist.";
				return false;
			}
			$pmethod_id=$payment_method["pmethod_id"];
			if (!$this->db->deleteRecords('tbl_ppc_payment_method_fields', array('smt' => 'ppcpmf_pmethod_id = ?', 'vals' => array($pmethod_id)))){
				$this->error = $this->db->getError();
				return false;
			}
			foreach ($arr_payment_settings as $key=>$val){
				    if ($key!="btn_submit"){
						if (!is_array($val)) {
							$this->db->insert_from_array('tbl_ppc_payment_method_fields', array('ppcpmf_pmethod_id'=>$pmethod_id,'ppcpmf_key'=>$key,'ppcpmf_value'=>$val,'ppcpmf_serialized'=>0),true,array("IGNORE"));
						}else{
							$this->db->insert_from_array('tbl_ppc_payment_method_fields', array('ppcpmf_pmethod_id'=>$pmethod_id,'ppcpmf_key'=>$key,'ppcpmf_value'=>serialize($val),'ppcpmf_serialized'=>1),true,array("IGNORE"));
						}
					}
			}
			return true;
		}
    
	
   
}