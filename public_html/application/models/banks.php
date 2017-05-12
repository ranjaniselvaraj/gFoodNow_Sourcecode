<?php
class Banks extends Model {
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
	
	function getBanks($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('bank_name','ASC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
		
	}
	
	function search($criteria, $count='') {
        $srch = new SearchBase('tbl_banks', 'tb');
        if($count==true) {
            $srch->addFld('COUNT(tb.bank_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tb.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tb.bank_id', '=', intval($val));
                break;
			case 'keyword':
				$srch->addDirectCondition('(tb.bank_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tb.bank_name like '. $this->db->quoteVariable('%' . $val . '%') .')');
                break;
			case 'page':
				$srch->setPageNumber($val);
			break;	
			case 'pagesize':
				$srch->setPageSize($val);
			break;		
            
            }
        }
        return $srch;
    }
	
	
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT bank_id, bank_name FROM tbl_banks WHERE 1=1 ";
        if (!empty($not_to_include)) $query .= " AND bank_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY bank_name";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function addUpdate($data){
		$bank_id = intval($data['bank_id']);
		$record = new TableRecord('tbl_banks');
		$assign_fields = array();
		$assign_fields['bank_name'] = $data['bank_name'];
		$record->assignValues($assign_fields);
		if($bank_id === 0 && $record->addNew()){
			$this->bank_id=$record->getId();
		}elseif($bank_id > 0 && $record->update(array('smt'=>'bank_id=?', 'vals'=>array($bank_id)))){
			$this->bank_id=$bank_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->bank_id;
	}
	
	function delete($bank_id){
		$bank_id = intval($bank_id);
		if($bank_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_banks', array('smt'=>'bank_id=?', 'vals'=>array($bank_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
}