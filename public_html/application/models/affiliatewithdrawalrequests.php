<?php
class AffiliateWithdrawalRequests extends Model {
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
		$srch->addOrder('afwithdrawal_id','DESC');
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
		
		$srch = new SearchBase('tbl_affiliate_transactions', 'atxn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('atxn.atxn_affiliate_id');
		$srch->addMultipleFields(array('atxn.atxn_affiliate_id',"SUM(atxn_credit-atxn_debit) as affiliateBalance"));
		$qry_affiliate_balance = $srch->getQuery();
		
        $srch = new SearchBase('tbl_affiliate_withdrawal_requests', 'tawr');
		$srch->joinTable('tbl_affiliates', 'INNER JOIN', 'tawr.afwithdrawal_affiliate_id=ta.affiliate_id', 'ta');
		$srch->joinTable('(' . $qry_affiliate_balance . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqab.atxn_affiliate_id', 'tqab');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
			case 'id':
                $srch->addCondition('tawr.afwithdrawal_id', '=', intval($val));
                break;
			case 'affiliate':
                $srch->addCondition('tawr.afwithdrawal_affiliate_id', '=', intval($val));
                break;
			case 'keyword':
            	$srch->addDirectCondition('(ta.affiliate_username LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR ta.affiliate_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR ta.affiliate_email LIKE '. $this->db->quoteVariable('%' . $val . '%') .' )');
            break;		
			case 'status':
                $srch->addCondition('tawr.afwithdrawal_status', '=', intval($val));
                break;	
			case 'minprice':
                $srch->addCondition('tawr.afwithdrawal_amount', '>=', $val);
                break;
			case 'maxprice':
                $srch->addCondition('tawr.afwithdrawal_amount', '<=', $val);
                break;
			case 'date_from':
                $srch->addCondition('tawr.afwithdrawal_request_date', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tawr.afwithdrawal_request_date', '<=', $val. ' 23:59:59');
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
	
	function updateWithdrawalRequestStatus($withdrawal_request_id,$data_update=array()) {
		$withdrawal_request_id = intval($withdrawal_request_id);
		if($withdrawal_request_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_affiliate_withdrawal_requests', $data_update, array('smt'=>'`afwithdrawal_id` = ?', 'vals'=> array($withdrawal_request_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
}