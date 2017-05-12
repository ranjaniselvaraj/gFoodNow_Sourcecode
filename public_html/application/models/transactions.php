<?php
class Transactions extends Model {
	
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
		$srch = new SearchBase('tbl_user_transactions', 'tut');
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
	        switch($key) {
    	    	case 'id':
        	    	$srch->addCondition('tut.utxn_id', '=', intval($val));
            	break;
				case 'user':
    		        $srch->addCondition('tut.utxn_user_id', '=', intval($val));
        	    break;
				case 'withdrawal_request':
	            	$srch->addCondition('tut.utxn_withdrawal_id', '=', intval($val));
	            break;
		      }
        }
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('tut.*',"utxn_credit -utxn_debit  bal"));
		$qry_user_points_balance = $srch->getQuery();
		
		
        $srch = new SearchBase('tbl_user_transactions', 'tut');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tut.utxn_user_id=tu.user_id', 'tu');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tut.utxn_order_id=torp.opr_id', 'torp');
		if($count==true) {
            $srch->addMultipleFields(array('IFNULL(SUM(tut.utxn_credit),0) AS total_earned','IFNULL(SUM(tut.utxn_debit),0) AS total_used'));
        }else{
			$srch->joinTable('(' . $qry_user_points_balance . ')', 'JOIN', 'tqupb.utxn_id <= tut.utxn_id', 'tqupb');
			$srch->addMultipleFields(array('tut.*','tu.*','torp.*',"SUM(tqupb.bal) balance"));
			$srch->addGroupBy('tut.utxn_id');
		}
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
		        switch($key) {
	    		   	 case 'id':
    	        		$srch->addCondition('tut.utxn_id', '=', intval($val));
		        	 break;
					 case 'withdrawal_request':
    	        		$srch->addCondition('tut.utxn_withdrawal_id', '=', intval($val));
		        	 break;
					 case 'page':
						$srch->setPageNumber($val);
					break;	
					case 'pagesize':
						$srch->setPageSize($val);
					break;
					case 'user':
	            		$srch->addCondition('tut.utxn_user_id', '=', intval($val));
		    	    break;
	    	    }
    	}
		$srch->addOrder('tut.utxn_id', 'desc');
		return $srch;
	}
	
	function getTransactionSummary($criterias){
		$srch = self::search($criterias,true);
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getTransactions($criterias,$pagesize){
		$srch = self::search($criterias);
		$srch->setPagesize($pagesize);	
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		$transactions = array();
		while($row = $this->db->fetch($rs)){
			$this->txn_id=$row["utxn_id"];
			$this->loadData();
			$row['formatted_transaction_number'] = $this->format_transaction_number($row["utxn_id"]);
			$row['formatted_comments'] = $this->format_transaction_comments($row["utxn_comments"]);
			$transactions[$row['utxn_id']] = $row;
		}
		return $transactions;
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
	
	function format_transaction_number($txn_id){
		$new_value=str_pad($txn_id,7,'0',STR_PAD_LEFT);
		$new_value="TN"."-".$new_value;
		return $new_value;
	}
	
	function format_transaction_comments($txn_comments){
		$strComments=$txn_comments;
		$strComments=preg_replace('/<\/?a[^>]*>/','',$strComments);
		return $strComments;
	}
	
	
	
	function addTransaction($data){
		$user_id = intval($data['utxn_user_id']);
		unset($data['utxn_user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_transactions');
		$data['utxn_user_id'] = $user_id;
		$data["utxn_date"]=date('Y-m-d H:i:s');
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