<?php
class SubscriptionOrderstatus extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getData($id) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
	
	function getOrderStatuses($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('sorder_status_priority','ASC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
		
	}
	
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_subscription_order_status', 'tos');
		$srch->addCondition('tos.sorder_status_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tos.sorder_status_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tos.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tos.sorder_status_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tos.sorder_status_name', 'like', '%'.$val.'%');
                break;	
            
            }
        }
        return $srch;
    }
	
	function getAssociativeArray($in_array=array(),$current) {
		$query = "SELECT sorder_status_id, sorder_status_name FROM tbl_subscription_order_status WHERE sorder_status_is_deleted='0' ";
        if ($current>0) 
			$query .= " AND priority >= (SELECT priority FROM tbl_subscription_order_status WHERE sorder_status_is_deleted='0' and sorder_status_id = {$current})";
			
		if (count($in_array)>0)
			$query .= " AND sorder_status_id IN (".implode(",",$in_array).")";	
			
		$query .= " ORDER BY sorder_status_priority";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function addUpdate($data){
		$sorder_status_id = intval($data['sorder_status_id']);
		$record = new TableRecord('tbl_subscription_order_status');
		$assign_fields = array();
		$assign_fields['sorder_status_name'] = $data['sorder_status_name'];
		$assign_fields['sorder_status_priority'] = intval($data['sorder_status_priority']);
		$assign_fields['sorder_status_subscriber_flag'] = intval($data['sorder_status_subscriber_flag']);
		
		$record->assignValues($assign_fields);
		if($sorder_status_id === 0 && $record->addNew()){
			$this->sorder_status_id=$record->getId();
		}elseif($sorder_status_id > 0 && $record->update(array('smt'=>'sorder_status_id=?', 'vals'=>array($sorder_status_id)))){
			$this->sorder_status_id=$sorder_status_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->sorder_status_id;
	}
	
	function delete($sorder_status_id){
		$sorder_status_id = intval($sorder_status_id);
		if($sorder_status_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_subscription_order_status', array('sorder_status_is_deleted' => 1), array('smt' => 'sorder_status_id = ?', 'vals' => array($sorder_status_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
   
}