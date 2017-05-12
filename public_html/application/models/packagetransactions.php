<?php
class PackageTransactions extends Model {
	
	function __construct() {
		$this->db = Syspage::getdb();
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
	
	function addTransaction($data){
		$record = new TableRecord('tbl_subscription_merchant_package_transactions');
		$data["mptran_date"]= date('Y-m-d H:i:s');// 'mysql_func_NOW()';
		$record->assignValues($data);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function getTransactionById($id, $add_criteria=array()) {
		if ($id>0)
        $add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getTransactions($criterias,$pagesize=10){
		$srch = self::search($criterias);
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}
		if( intval($pagesize)<=0 && $criterias['pagesize']<=0 ){
			$srch->doNotLimitRecords();
		}
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if( isset($criterias['gateway_transaction_id']) || isset($criterias['mptran_gateway_transaction_id']) ){
			return $this->db->fetch($rs);
		}
		return $this->db->fetch_all($rs);
	}
	function getTransactionsByProfileId($criterias,$pagesize){
		$srch = subscriptionorders::search($criterias);
		$srch->joinTable('tbl_subscription_merchant_package_transactions','INNER JOIN','mporder_id=mptran_mporder_id','mp');
		
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}
		if( intval($pagesize)<=0 && $criterias['pagesize']<=0 ){
			$srch->doNotLimitRecords();
		}
		$srch->addMultipleFields(array('mp.*'));
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if( isset($criterias['gateway_transaction_id']) || isset($criterias['mptran_gateway_transaction_id'])|| isset($criterias['mporder_gateway_subscription_id']) ){
			return $this->db->fetch($rs);
		}
		return $this->db->fetch_all($rs);
	}
	
	function getLatestTransByOrderId($mporderId){
		$criteria = array('mptran_mporder_id' => $mporderId);
		$srch = self::search($criteria);
		$recurrTypeCond = $srch->addCondition('tmpt.mptran_recurring_type', '=', -1);
		$recurrTypeCond->attachCondition('tmpt.mptran_recurring_type', '=', 1);
		$srch->addOrder('tmpt.mptran_date', 'desc');
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs); // get latest transaction by order
	}
	
	function search($criteria){
        $srch = new SearchBase('tbl_subscription_merchant_package_transactions', 'tmpt');
		
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
		        switch($key) {
	    		   	 case 'id':
    	        		$srch->addCondition('tmpt.mptran_id', '=', intval($val));
		        	 break;
					 case 'mptran_mporder_id':
						$srch->addCondition('tmpt.mptran_mporder_id', '=', intval($val));
					 break;
					 case 'gateway_transaction_id':
					 case 'mptran_gateway_transaction_id':
						$srch->addCondition('tmpt.mptran_gateway_transaction_id', '=', $val);
					 break;
					 case 'page':
						$srch->setPageNumber($val);
					break;	
					case 'pagesize':
						$srch->setPageSize($val);
					break;
	    	    }
    	}
		$srch->addCondition('mptran_active_from','!=','0000-00-00 00:00:00');
		$srch->addOrder('tmpt.mptran_id', 'desc');
		return $srch;
	}
	
	function updateTransactionInfo( $mptran_id, $data ){
		$record = new TableRecord('tbl_subscription_merchant_package_transactions');
		$record->assignValues($data);
		if(!$record->update(array('smt'=>'mptran_id=?', 'vals'=>array($mptran_id)))){
			$this->error = $this->db->getError();
			return false;
		}
		return true;
	}
}
?>