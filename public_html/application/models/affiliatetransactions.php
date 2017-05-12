<?php
class Affiliatetransactions extends Model {
	
	function __construct($id=0) {
		$this->db = Syspage::getdb();
        if (!is_numeric($id))
            $id = 0;
        $this->txn_id = intval($id);
        if (!($this->txn_id > 0)) {
            return;
        }
        $this->loadData();
    }
	
	protected function loadData() {
        $this->attributes = self::getTransactionById($this->txn_id);
    }
	function getData() {
        return $this->attributes;
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
	
	function search($criteria,$count=false){
		$srch = new SearchBase('tbl_affiliate_transactions', 'tat');
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
	        switch($key) {
    	    	case 'id':
        	    	$srch->addCondition('tat.atxn_id', '=', intval($val));
            	break;
				case 'affiliate':
    		        $srch->addCondition('tat.atxn_affiliate_id', '=', intval($val));
        	    break;
				case 'withdrawal_request':
	            	$srch->addCondition('tat.atxn_withdrawal_id', '=', intval($val));
	            break;
		      }
        }
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('tat.*',"atxn_credit -atxn_debit  bal"));
		$qry_affiliate_points_balance = $srch->getQuery();
		
		
        $srch = new SearchBase('tbl_affiliate_transactions', 'tat');
		$srch->joinTable('tbl_affiliates', 'INNER JOIN', 'tat.atxn_affiliate_id=ta.affiliate_id', 'ta');
		if($count==true) {
            $srch->addMultipleFields(array('IFNULL(SUM(tat.atxn_credit),0) AS total_earned','IFNULL(SUM(tat.atxn_debit),0) AS total_used'));
        }else{
			$srch->joinTable('(' . $qry_affiliate_points_balance . ')', 'JOIN', 'tqapb.atxn_id <= tat.atxn_id', 'tqapb');
			$srch->addMultipleFields(array('tat.*','ta.*',"SUM(tqapb.bal) balance"));
			$srch->addGroupBy('tat.atxn_id');
		}
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
		        switch($key) {
	    		   	 case 'id':
    	        		$srch->addCondition('tat.atxn_id', '=', intval($val));
		        	 break;
					 case 'withdrawal_request':
    	        		$srch->addCondition('tat.atxn_withdrawal_id', '=', intval($val));
		        	 break;
					 case 'page':
						$srch->setPageNumber($val);
					 break;	
					 case 'pagesize':
						$srch->setPageSize($val);
					 break;
					 case 'affiliate':
	            		$srch->addCondition('tat.atxn_affiliate_id', '=', intval($val));
		    	     break;
	    	    }
    	}
		$srch->addOrder('tat.atxn_id', 'desc');
		return $srch;
	}
	
	function getTransactionSummary($criterias){
		$srch = self::search($criterias,true);
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getTransactions($criterias,$pagesize){
        foreach($criterias as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
		//die($srch->getquery());
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	
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
	
	
	
	function addAffiliateTransaction($data){
		$affiliate_id = intval($data['atxn_affiliate_id']);
		unset($data['atxn_affiliate_id']);
		if($affiliate_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_affiliate_transactions');
		$data['atxn_affiliate_id'] = $affiliate_id;
		$data["atxn_date"]=date('Y-m-d H:i:s');
		$record->assignValues($data);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	
}