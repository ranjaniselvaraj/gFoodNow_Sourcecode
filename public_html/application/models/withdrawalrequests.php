<?php
class WithdrawalRequests extends Model {
	 function __construct(){
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
	
	function getWithdrawRequests($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search_withdrawal_requests($add_criteria);
		$srch->addOrder('withdrawal_id','DESC');
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
	
	function getWithdrawRequestData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search_withdrawal_requests($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function search_withdrawal_requests($criteria, $count='') {
		
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
		
        $srch = new SearchBase('tbl_user_withdrawal_requests', 'tuwr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tuwr.withdrawal_user_id=tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		//$srch->joinTable('tbl_banks', 'INNER JOIN', 'tuwr.withdrawal_bank=tb.bank_id', 'tb');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
			case 'id':
                $srch->addCondition('tuwr.withdrawal_id', '=', intval($val));
                break;
			case 'user':
                $srch->addCondition('tuwr.withdrawal_user_id', '=', intval($val));
                break;
			case 'keyword':
            	$srch->addDirectCondition('(tu.user_username LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tu.user_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tu.user_email LIKE '. $this->db->quoteVariable('%' . $val . '%') .' )');
            break;		
			case 'status':
                $srch->addCondition('tuwr.withdrawal_status', '=', intval($val));
                break;	
			case 'minprice':
                $srch->addCondition('tuwr.withdrawal_amount', '>=', $val);
                break;
			case 'maxprice':
                $srch->addCondition('tuwr.withdrawal_amount', '<=', $val);
                break;
			case 'date_from':
                $srch->addCondition('tuwr.withdrawal_request_date', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tuwr.withdrawal_request_date', '<=', $val. ' 23:59:59');
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
	
	function updateWithdrawalRequestStatus($withdrawal_id,$data_update=array()) {
		$withdrawal_id = intval($withdrawal_id);
		if($withdrawal_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_user_withdrawal_requests', $data_update, array('smt'=>'`withdrawal_id` = ?', 'vals'=> array($withdrawal_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
}