<?php
class Productfeedbacks extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getProductFeedback($id,$add_criteria=array()) {
        $id = intval($id);
        if ($id>0) $add_criteria['id'] = $id;
        $srch = self::search_product_feedbacks($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $srch->doNotLimitRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getFeedbacksWithCriteria($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search_product_feedbacks($add_criteria);
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $arr_feedbacks=$this->db->fetch_all($rs);
		return $arr_feedbacks;
    }
	
	function search_product_feedbacks($criteria, $count='') {
		$srch = new SearchBase('tbl_prod_reviews', 'tpr');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tpr.review_prod_id=tp.prod_id', 'tp');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tp.prod_shop=ts.shop_id', 'ts');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tpr.review_user_id=tu.user_id', 'tu');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tpr.review_order=torp.opr_id and torp.opr_product_id=tpr.review_prod_id', 'torp');
		//$srch->addCondition('tpr.review_status', '=', 1);
		$srch->addCondition('tpr.review_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tpr.review_id', '=', intval($val));
    	            break;
			   case 'order':
                	$srch->addCondition('tpr.review_order', '=', intval($val));
	                break;
			   case 'reviewed_by_id':
                	$srch->addCondition('tu.user_id', '=', intval($val));
	                break;
			   case 'product_id':
			   case 'product': 
                	$srch->addCondition('tpr.review_prod_id', '=', intval($val));
	                break;
			   case 'shop_id':
			   case 'shop':
                	$srch->addCondition('tp.prod_shop', '=', $val);
	                break;
			   case 'status':
			   		 if (is_array($val)){
	               		 $srch->addCondition('tpr.review_status', '=', $val);
					 }else{
						 $srch->addCondition('tpr.review_status', '=', $val);
					 }
               break;		
			   case 'date_from':
               	$srch->addCondition('tpr.reviewed_on', '>=', $val. ' 00:00:00');
               break;
			   case 'date_to':
               	$srch->addCondition('tpr.reviewed_on', '<=', $val. ' 23:59:59');
               break;
			   case 'pagesize':
					$srch->setPageSize($val);
			   break;
			   case 'page':
					$srch->setPageNumber($val);
			   break;					
            }
        }
        $srch->addOrder('tpr.review_id', 'desc');
        return $srch;
    }
	
	function updateProductFeedback($review_id,$data_update=array()) {
		$review_id = intval($review_id);
		if($review_id < 1 || count($data_update) < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_prod_reviews', $data_update, array('smt'=>'`review_id` = ?', 'vals'=> array($review_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
	
	
}