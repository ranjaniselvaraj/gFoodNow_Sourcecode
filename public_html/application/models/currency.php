<?php
class Currency extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT currency_code, currency_title FROM tbl_currencies WHERE 1=1 ";
        if (!empty($not_to_include)) $query .= " AND currency_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY currency_title";
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
    
    function search($criteria) {
        $srch = new SearchBase('tbl_currencies', 'tc');
        $srch->addMultipleFields(array('tc.*'));
        foreach($criteria as $key=>$val) {
            switch($key) {
            	case 'id':
                	$srch->addCondition('tc.currency_id', '=', intval($val));
                break;
				case 'code':
                	$srch->addCondition('tc.currency_code', '=', $val);
                break;
            }
        }
        $srch->addOrder('tc.currency_id', 'desc');
        return $srch;
    }
	
	function getAllCurrencyRecords($criteria=array()){
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
		$currency_id = intval($data['currency_id']);
		$record = new TableRecord('tbl_currencies');
		$assign_fields = array();
		$assign_fields['currency_title'] = $data['currency_title'];
		$assign_fields['currency_code'] = $data['currency_code'];
		$assign_fields['currency_decimal'] = $data['currency_decimal'];
		$assign_fields['currency_symbol_left'] = $data['currency_symbol_left'];
		$assign_fields['currency_symbol_right'] = $data['currency_symbol_right'];
		$assign_fields['currency_value'] = 1;
		$record->assignValues($assign_fields);
		if($currency_id === 0 && $record->addNew()){
			$this->currency_id=$record->getId();
		}elseif($currency_id > 0 && $record->update(array('smt'=>'currency_id=?', 'vals'=>array($currency_id)))){
			$this->currency_id=$currency_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->currency_id;
	}
	
	function getCurrencyByCode($name) {
        if($name=="") return array();
       	$add_criteria['code'] = $name;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function delete($currency_id){
		$currency_id = intval($currency_id);
		if($currency_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_currencies', array('smt'=>'currency_id=?', 'vals'=>array($currency_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
	
}