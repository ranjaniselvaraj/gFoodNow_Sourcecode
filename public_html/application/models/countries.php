<?php
class Countries extends Model{
    function __construct(){
		$this->db = Syspage::getdb();
    }
	function getCountryId() {
        return $this->country_id;
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
	
	function getCountries($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
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
	
	function search($criteria, $count='') {
        $srch = new SearchBase('tbl_countries', 'tc');
		$srch->addCondition('tc.country_delete', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tc.country_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tc.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tc.country_id', '=', intval($val));
                break;
			case 'name':
                $srch->addCondition('tc.country_name', '=', $val);
                break;
			case 'keyword':
                $srch->addCondition('tc.country_name', 'like', '%'.$val.'%');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
            
            }
        }
        $srch->addOrder('tc.country_name', 'asc');
        return $srch;
    }
	
	function addUpdate($data){
		$country_id = intval($data['country_id']);
		$record = new TableRecord('tbl_countries');
		$assign_fields = array();
		$assign_fields['country_code'] = $data['country_code'];
		$assign_fields['country_name'] = $data['country_name'];
		$record->assignValues($assign_fields);
		if($country_id === 0 && $record->addNew()){
			$this->country_id=$record->getId();
		}elseif($country_id > 0 && $record->update(array('smt'=>'country_id=?', 'vals'=>array($country_id)))){
			$this->country_id=$country_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->country_id;
	}
	
	function delete($country_id){
		$country_id = intval($country_id);
		if($country_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_countries', array('country_delete' => 1), array('smt' => 'country_id = ?', 'vals' => array($country_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getAssociativeArray(){
		$srch = new SearchBase('tbl_countries', 'c');
		$srch->addCondition('country_delete', '=',0);
		$srch->addMultipleFields(array('country_id', 'country_name'));
		$srch->addOrder('country_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function getCountryByName($name) {
        if($name=="") return array();
       	$add_criteria['name'] = $name;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
   
}
