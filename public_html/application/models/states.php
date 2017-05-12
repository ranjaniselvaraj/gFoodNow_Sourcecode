<?php
class States extends Model{
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getStateId() {
        return $this->state_id;
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
	
	function getAssociativeArray($country_id=0){
		$srch = new SearchBase('tbl_states', 's');
		$srch->addCondition('state_delete', '=',0);
		if($country_id > 0) $srch->addCondition('country_id', '=', $country_id);
		$srch->addMultipleFields(array('state_id', 'state_name'));
		$srch->addOrder('state_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function getStatesAssoc($country_id=0){
		$country_id = intval($country_id);
		if($country_id < 1) return array();
		$srch = new SearchBase('tbl_states', 's');
		$srch->addCondition('state_delete', '=',0);
		$srch->addCondition('country_id', '=', $country_id);
		$srch->addMultipleFields(array('state_id', 'state_name'));
		$srch->addOrder('state_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function search($criteria, $count='') {
        $srch = new SearchBase('tbl_states', 'ts');
		$srch->joinTable('tbl_countries', 'INNER JOIN', 'ts.country_id=tc.country_id and country_delete=0', 'tc');
		$srch->joinTable('tbl_zones', 'INNER JOIN', 'ts.zone_id=tz.zone_id and zone_delete=0', 'tz');
		$srch->addCondition('ts.state_delete', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(ts.state_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('ts.*','tc.country_name','tz.zone_name'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('ts.state_id', '=', intval($val));
                break;
			case 'name':
                $srch->addCondition('ts.state_name', '=', ($val));
                break;	
			case 'country':
				if ($val>0){
                	$srch->addCondition('ts.country_id', '=', intval($val));
				}
                break;	
			case 'keyword':
                $srch->addCondition('ts.state_name', 'like', '%'.$val.'%');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;		
			case 'limit':
                $srch->setPageSize($val);
                break;		
            
            }
        }
        return $srch;
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
	
	function getStates($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('tc.country_name', 'asc');
		$srch->addOrder('ts.state_name', 'asc');
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
	
	function addUpdate($data){
		$state_id = intval($data['state_id']);
		$record = new TableRecord('tbl_states');
		$assign_fields = array();
		$assign_fields['zone_id'] = intval($data['zone_id']);
		$assign_fields['country_id'] = intval($data['country_id']);
		$assign_fields['state_code'] = $data['state_code'];
		$assign_fields['state_name'] = $data['state_name'];
		$record->assignValues($assign_fields);
		if($state_id === 0 && $record->addNew()){
			$this->state_id=$record->getId();
		}elseif($state_id > 0 && $record->update(array('smt'=>'state_id=?', 'vals'=>array($state_id)))){
			$this->state_id=$state_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->state_id;
	}
	
	function delete($state_id){
		$state_id = intval($state_id);
		if($state_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_states', array('state_delete' => 1), array('smt' => 'state_id = ?', 'vals' => array($state_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getStateByName($name) {
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
