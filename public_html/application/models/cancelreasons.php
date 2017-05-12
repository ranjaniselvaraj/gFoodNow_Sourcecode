<?php
class Cancelreasons extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT cancelreason_id, cancelreason_title FROM tbl_cancel_reasons WHERE 1=1 ";
        if (!empty($not_to_include)) $query .= " AND cancelreason_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY cancelreason_title";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
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
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_cancel_reasons', 'tcr');
        if($count==true) {
            $srch->addFld('COUNT(tcr.cancelreason_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tcr.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tcr.cancelreason_id', '=', intval($val));
                break;
			case 'keyword':
				$srch->addDirectCondition('(tcr.cancelreason_title LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tcr.cancelreason_description like '. $this->db->quoteVariable('%' . $val . '%') .')');
                break;	
            
            }
        }
        $srch->addOrder('tcr.cancelreason_id', 'desc');
        return $srch;
    }
	
	function getAllCancelReasons($criteria=array()){
		$add_criteria = array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$rs = $srch->getResultSet();
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
	}
	
	function addUpdate($data){
		$cancelreason_id = intval($data['cancelreason_id']);
		$record = new TableRecord('tbl_cancel_reasons');
		$assign_fields = array();
		$assign_fields['cancelreason_title'] = $data['cancelreason_title'];
		$assign_fields['cancelreason_description'] = $data['cancelreason_description'];
		$record->assignValues($assign_fields);
		if($cancelreason_id === 0 && $record->addNew()){
			$this->cancelreason_id=$record->getId();
		}elseif($cancelreason_id > 0 && $record->update(array('smt'=>'cancelreason_id=?', 'vals'=>array($cancelreason_id)))){
			$this->cancelreason_id=$cancelreason_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->cancelreason_id;
	}
	
	function delete($cancelreason_id){
		$cancelreason_id = intval($cancelreason_id);
		if($cancelreason_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_cancel_reasons', array('smt'=>'cancelreason_id=?', 'vals'=>array($cancelreason_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}