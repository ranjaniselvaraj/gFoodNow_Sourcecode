<?php
class CancelRequests extends Model {
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
	
	function getCancelRequests($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search_cancel_requests($add_criteria);
		$srch->addOrder('cancellation_request_id','DESC');
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
	
	function getCancelRequest($id,$add_criteria=array()) {
        $id = intval($id);
        if ($id>0) $add_criteria['id'] = $id;
        $srch = self::search_cancel_requests($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $srch->doNotLimitRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getCancelRequestByOrder($order) {
        $order = intval($order);
        if ($order>0) $add_criteria['order'] = $order;
        $srch = self::search_cancel_requests($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $srch->doNotLimitRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function search_cancel_requests($criteria, $count='') {
       $srch = new SearchBase('tbl_order_cancel_requests', 'tpcr');
		$srch->joinTable('tbl_order_products', 'INNER JOIN', 'tpcr.cancellation_request_order=torp.opr_id', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'torp.opr_order_id=tord.order_id', 'tord');
		$srch->joinTable('tbl_orders_status', 'LEFT JOIN', 'torp.opr_status = tors.orders_status_id', 'tors');
		$srch->joinTable('tbl_cancel_reasons', 'LEFT JOIN', 'tpcr.cancellation_request_reason=trr.cancelreason_id', 'trr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tpcr.cancellation_request_user_id=tb.user_id', 'tb');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'torp.opr_product_shop=ts.shop_id', 'ts');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tv.user_id', 'tv');
		$srch->addMultipleFields(array('tpcr.*','tb.*','torp.*','tord.order_id','trr.*','tors.orders_status_name','tb.user_name as buyer_name','tb.user_email as buyer_email','tv.user_name as vendor_name','tv.user_email as vendor_email'));
		
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
					case 'id':
						$srch->addCondition('tpcr.cancellation_request_id', '=', intval($val));
						break;
					case 'return':
			   			$user=$criteria["user"];
				   		if ($val=="all"){
							$cnd=$srch->addCondition('tpcr.cancellation_request_user_id', '=', intval($user));
							$cnd->attachCondition('tv.user_id', '=',$user,'OR');
						}elseif ($val=="sent"){
							$srch->addCondition('tpcr.cancellation_request_user_id', '=', intval($user));
						}
						elseif ($val=="received"){
							$srch->addCondition('tv.user_id', '=', intval($user));
						}
					break;
					
					case 'keyword':
		            	$srch->addDirectCondition('(tb.user_username LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tb.user_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tb.user_email LIKE '. $this->db->quoteVariable('%' . $val . '%') .' )');
        		    break;		
					case 'buyer':
						$cnd=$srch->addCondition('tpcr.cancellation_request_user_id', '=', intval($val));
						break;
					case 'seller':
						$cnd=$srch->addCondition('tu.user_id', '=', intval($val));
						break;
					case 'order':
						$srch->addCondition('tpcr.cancellation_request_order', '=', intval($val));
						break;
					case 'status':	
						if (is_array($val))	
							$srch->addCondition('tpcr.cancellation_request_status', 'IN', $val);
						else
						$srch->addCondition('tpcr.cancellation_request_status', '=', intval($val));	
						break;
					case 'date_from':
						$srch->addCondition('tpcr.cancellation_request_date', '>=', $val. ' 00:00:00');
						break;
					case 'date_to':
						$srch->addCondition('tpcr.cancellation_request_date', '<=', $val. ' 23:59:59');
						break;
					case 'page':
					$srch->setPageNumber($val);
						break;	
					case 'pagesize':
		            	$srch->setPageSize($val);
	    		    break;		
            }
        }
		$srch->addOrder("case when cancellation_request_status = '0' then 1 else 2 end",'ASC');
		$srch->addOrder('tpcr.cancellation_request_id', 'desc');
        return $srch;
    }
	
	function updateCancellationRequestStatus($cancel_request_id,$data_update=array()) {
		$cancel_request_id = intval($cancel_request_id);
		if($cancel_request_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_order_cancel_requests', $data_update, array('smt'=>'`cancellation_request_id` = ?', 'vals'=> array($cancel_request_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}