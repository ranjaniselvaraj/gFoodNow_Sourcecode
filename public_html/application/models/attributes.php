<?php
class Attributes extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
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
	
	function getAttributes($criteria) {
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
        $srch = new SearchBase('tbl_attributes', 'tatt');
		$srch->joinTable('tbl_attribute_groups', 'INNER JOIN', 'tatt.attribute_group = tag.attribute_group_id and tag.attribute_group_is_deleted=0', 'tag');
		$srch->addCondition('tatt.attribute_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tatt.attribute_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tatt.*','tag.*'));
        }
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tatt.attribute_id', '=', intval($val));
                break;
			case 'group':
                $srch->addCondition('tatt.attribute_group', '=', intval($val));
                break;	
			case 'keyword':
                $cndCondition=$srch->addCondition('tatt.attribute_name', 'like', '%'.$val.'%');
				$cndCondition->attachCondition('tag.attribute_group_name', 'like', '%'.$val.'%','OR');
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
		$srch->addOrder('tatt.attribute_display_order', 'asc');
        return $srch;
    }
	
	function addUpdateAttribute($data){
		$attribute_id = intval($data['attribute_id']);
		$record = new TableRecord('tbl_attributes');
		$assign_fields = array();
		$assign_fields['attribute_name'] = $data['attribute_name'];
		$assign_fields['attribute_group'] = intval($data['attribute_group']);
		$assign_fields['attribute_display_order'] = intval($data['attribute_display_order']);
		$record->assignValues($assign_fields);
		if($attribute_id === 0 && $record->addNew()){
			return $record->getId();
		}elseif($attribute_id > 0 && $record->update(array('smt'=>'attribute_id=?', 'vals'=>array($attribute_id)))){
			return $attribute_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function delete($attribute_id){
		$attribute_id = intval($attribute_id);
		if($attribute_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_attributes', array('attribute_is_deleted' => 1), array('smt' => 'attribute_id = ?', 'vals' => array($attribute_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
    
   
}