<?php
class Filtergroupoptions extends Model {
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
	
	function getFilterGroupOptions($criteria) {
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
        $srch = new SearchBase('tbl_filters', 'tf');
		$srch->joinTable('tbl_filter_groups', 'INNER JOIN', 'tf.filter_group = tfg.filter_group_id and tfg.filter_group_is_deleted=0', 'tfg');
		$srch->addCondition('tf.filter_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(tf.filter_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tf.*','tfg.*'));
        }
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tf.filter_id', '=', intval($val));
                break;
			case 'group':
                $srch->addCondition('tf.filter_group', '=', intval($val));
                break;	
			case 'keyword':
                $cndCondition=$srch->addCondition('tf.filter_name', 'like', '%'.$val.'%');
				$cndCondition->attachCondition('tfg.filter_group_name', 'like', '%'.$val.'%','OR');
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
		$srch->addOrder('tf.filter_display_order', 'asc');
		$srch->addOrder('tf.filter_name', 'asc');
        return $srch;
    }
	
	function addUpdateFilterGroupOption($data){
		$filter_id = intval($data['filter_id']);
		$record = new TableRecord('tbl_filters');
		$assign_fields = array();
		$assign_fields['filter_name'] = $data['filter_name'];
		$assign_fields['filter_group'] = intval($data['filter_group']);
		$assign_fields['filter_display_order'] = intval($data['filter_display_order']);
		$record->assignValues($assign_fields);
		if($filter_id === 0 && $record->addNew()){
			return $record->getId();
		}elseif($filter_id > 0 && $record->update(array('smt'=>'filter_id=?', 'vals'=>array($filter_id)))){
			return $filter_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function delete($filter_id){
		$filter_id = intval($filter_id);
		if($filter_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_filters', array('filter_is_deleted' => 1), array('smt' => 'filter_id = ?', 'vals' => array($filter_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
    
   
}