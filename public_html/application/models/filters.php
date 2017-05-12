<?php
class Filters extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getFilterGroupId() {
        return $this->filter_group_id;
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
	
	function getFilters($criteria) {
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
        return $this->db->fetch_all($rs);
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
        $srch = new SearchBase('tbl_filter_groups', 'tfg');
		$srch->addCondition('tfg.filter_group_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tfg.filter_group_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tfg.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tfg.filter_group_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tfg.filter_group_name', 'like', '%'.$val.'%');
                break;
			case 'pagesize':
					$srch->setPageSize($val);
					break;
			case 'page':
					$srch->setPageNumber($val);
					break;			
            
            }
        }
        $srch->addOrder('tfg.filter_group_display_order', 'asc');
		$srch->addOrder('tfg.filter_group_name', 'asc');
        return $srch;
    }
	
	function addUpdate($data){
		$filter_group_id = intval($data['filter_group_id']);
		$record = new TableRecord('tbl_filter_groups');
		$assign_fields = array();
		$assign_fields['filter_group_name'] = $data['filter_group_name'];
		$assign_fields['filter_group_display_order'] = intval($data['filter_group_display_order']);
		$record->assignValues($assign_fields);
		if($filter_group_id === 0 && $record->addNew()){
			$this->filter_group_id=$record->getId();
		}elseif($filter_group_id > 0 && $record->update(array('smt'=>'filter_group_id=?', 'vals'=>array($filter_group_id)))){
			$this->filter_group_id=$filter_group_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		if (!$this->db->deleteRecords('tbl_filters', array('smt' => 'filter_group = ?', 'vals' => array($this->getFilterGroupId())))){
			$this->error = $this->db->getError();
			return false;
		}
		
		if (isset($data['filter_group_values'])) {
			foreach ($data['filter_group_values'] as $key=>$val){
				$record = new TableRecord('tbl_filters');
				$record->assignValues(array("filter_group"=>$this->getFilterGroupId()));
				$record->assignValues(array("filter_name"=>$val["name"],"filter_display_order"=>$val["sort"]));	
				if (isset($val['id'])){
					$record->setFldValue('filter_id',$val['id']);
				}
				if($val>0){
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		return $this->getFilterGroupId();
	}
	
	function delete($filter_group_id){
		$filter_group_id = intval($filter_group_id);
		if($filter_group_id < 1){
			$this->error = 'Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_filter_groups', array('filter_group_is_deleted' => 1), array('smt' => 'filter_group_id = ?', 'vals' => array($filter_group_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT filter_group_id, filter_group_name FROM tbl_filter_groups WHERE filter_group_is_deleted='0' ";
        if (!empty($not_to_include)) $query .= " AND filter_group_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY filter_group_name";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function getFilterValues($filter_group_id) {
		$filter_value_data = array();
		$rs = $this->db->query("SELECT * FROM tbl_filters WHERE filter_group = '" . (int)$filter_group_id . "' order by filter_display_order asc,filter_name asc");
		$filter_value_data=$this->db->fetch_all($rs);
		return $filter_value_data;
	}
    
   
}