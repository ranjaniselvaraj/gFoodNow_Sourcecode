<?php
class Attributegroups extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getAttributeGroupId() {
        return $this->attribute_group_id;
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
	
	function getAttributeGroups($criteria) {
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
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_attribute_groups', 'tag');
		$srch->addCondition('tag.attribute_group_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tag.attribute_group_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tag.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tag.attribute_group_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tag.attribute_group_name', 'like', '%'.$val.'%');
                break;	
			case 'pagesize':
					$srch->setPageSize($val);
					break;
			case 'page':
					$srch->setPageNumber($val);
					break;	
            
            }
        }
        $srch->addOrder('tag.attribute_group_display_order', 'asc');
        return $srch;
    }
	
	function addUpdate($data){
		$attribute_group_id = intval($data['attribute_group_id']);
		$record = new TableRecord('tbl_attribute_groups');
		$assign_fields = array();
		$assign_fields['attribute_group_name'] = $data['attribute_group_name'];
		$assign_fields['attribute_group_display_order'] = intval($data['attribute_group_display_order']);
		$record->assignValues($assign_fields);
		if($attribute_group_id === 0 && $record->addNew()){
			$this->attribute_group_id=$record->getId();
		}elseif($attribute_group_id > 0 && $record->update(array('smt'=>'attribute_group_id=?', 'vals'=>array($attribute_group_id)))){
			$this->attribute_group_id=$attribute_group_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		if (!$this->db->deleteRecords('tbl_attributes', array('smt' => 'attribute_group = ?', 'vals' => array($this->getAttributeGroupId())))){
			$this->error = $this->db->getError();
			return false;
		}
		
		if (isset($data['attribute_group_values'])) {
			foreach ($data['attribute_group_values'] as $key=>$val){
				$record = new TableRecord('tbl_attributes');
				$record->assignValues(array("attribute_group"=>$this->getAttributeGroupId()));
				$record->assignValues(array("attribute_name"=>$val["name"],"attribute_display_order"=>$val["sort"]));	
				if (isset($val['id'])){
					$record->setFldValue('attribute_id',$val['id']);
				}
				if($val>0){
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		return $this->getAttributeGroupId();
	}
	
	function delete($attribute_group_id){
		$attribute_group_id = intval($attribute_group_id);
		if($attribute_group_id < 1){
			$this->error = 'Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_attribute_groups', array('attribute_group_is_deleted' => 1), array('smt' => 'attribute_group_id = ?', 'vals' => array($attribute_group_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT attribute_group_id, attribute_group_name FROM tbl_attribute_groups WHERE attribute_group_is_deleted='0' ";
        if (!empty($not_to_include)) $query .= " AND attribute_group_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY attribute_group_name";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function getAttributeValues($attribute_group_id) {
		$attribute_group_value_data = array();
		$rs = $this->db->query("SELECT * FROM tbl_attributes WHERE attribute_group = '" . (int)$attribute_group_id . "' order by attribute_display_order asc,attribute_name asc");
		$attribute_group_value_data=$this->db->fetch_all($rs);
		return $attribute_group_value_data;
	}
    
   
}