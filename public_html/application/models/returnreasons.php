<?php
class Returnreasons extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT returnreason_id, returnreason_title FROM tbl_return_reasons WHERE 1=1 ";
        if (!empty($not_to_include)) $query .= " AND returnreason_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY returnreason_title";
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
        $srch = new SearchBase('tbl_return_reasons', 'trr');
        if($count==true) {
            $srch->addFld('COUNT(trr.returnreason_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('trr.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('trr.returnreason_id', '=', intval($val));
                break;
			case 'keyword':
				$srch->addDirectCondition('(trr.returnreason_title LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR trr.returnreason_description like '. $this->db->quoteVariable('%' . $val . '%') .')');
                break;	
            
            }
        }
        $srch->addOrder('trr.returnreason_id', 'desc');
        return $srch;
    }
	
	function getAllReturnReasons($criteria=array()){
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
		$returnreason_id = intval($data['returnreason_id']);
		$record = new TableRecord('tbl_return_reasons');
		$assign_fields = array();
		$assign_fields['returnreason_title'] = $data['returnreason_title'];
		$assign_fields['returnreason_description'] = $data['returnreason_description'];
		$record->assignValues($assign_fields);
		if($returnreason_id === 0 && $record->addNew()){
			$this->returnreason_id=$record->getId();
		}elseif($returnreason_id > 0 && $record->update(array('smt'=>'returnreason_id=?', 'vals'=>array($returnreason_id)))){
			$this->returnreason_id=$returnreason_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->returnreason_id;
	}
	
	function delete($returnreason_id){
		$returnreason_id = intval($returnreason_id);
		if($returnreason_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_return_reasons', array('smt'=>'returnreason_id=?', 'vals'=>array($returnreason_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}