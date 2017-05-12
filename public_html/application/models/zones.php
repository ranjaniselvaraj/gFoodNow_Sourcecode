<?php
class Zones extends Model{
    function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getZoneId() {
        return $this->zone_id;
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
	
	function getZones($criteria) {
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
        $srch = new SearchBase('tbl_zones', 'tz');
		$srch->addCondition('tz.zone_delete', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tz.zone_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tz.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tz.zone_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tz.zone_name', 'like', '%'.$val.'%');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
            }
        }
        $srch->addOrder('tz.zone_name', 'asc');
        return $srch;
    }
	
	function addUpdate($data){
		$zone_id = intval($data['zone_id']);
		$record = new TableRecord('tbl_zones');
		$assign_fields = array();
		$assign_fields['zone_name'] = $data['zone_name'];
		$assign_fields['zone_description'] = $data['zone_description'];
		$record->assignValues($assign_fields);
		if($zone_id === 0 && $record->addNew()){
			$this->zone_id=$record->getId();
		}elseif($zone_id > 0 && $record->update(array('smt'=>'zone_id=?', 'vals'=>array($zone_id)))){
			$this->zone_id=$zone_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->zone_id;
	}
	
	function delete($zone_id){
		$zone_id = intval($zone_id);
		if($zone_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_zones', array("zone_delete"=>1), array('smt'=>'`zone_id` = ?', 'vals'=> array($zone_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getAssociativeArray($not_to_include=null){
		$srch = new SearchBase('tbl_zones', 'tz');
		$srch->addCondition('zone_delete', '=',0);
		if (!empty($not_to_include)) 
			$srch->addCondition('zone_id', 'NOT IN',$not_to_include);
		$srch->addMultipleFields(array('zone_id', 'zone_name'));
		$srch->addOrder('zone_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	
}
