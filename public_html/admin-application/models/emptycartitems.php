<?php
class Emptycartitems extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
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
	
	function getAllEmptyCartItems($criteria=array()) {
		$add_criteria = array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('tci.emptycartitem_status', 'desc');
		$srch->addOrder('tci.emptycartitem_priority', 'asc');
		$rs = $srch->getResultSet();
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_empty_cart_items', 'tci');
		$srch->addCondition('tci.emptycartitem_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tci.emptycartitem_id', '=', intval($val));
                break;
			case 'status':
	            $srch->addCondition('tci.emptycartitem_status', '=', intval($val));
                break;	
			case 'limit':
                $srch->setPageSize($val);
                break;			
            }
        }
        return $srch;
    }
	
	function addUpdate($data){
		$emptycartitem_id = intval($data['emptycartitem_id']);
		$record = new TableRecord('tbl_empty_cart_items');
		$assign_fields = array();
		$assign_fields['emptycartitem_title'] = $data['emptycartitem_title'];
		$assign_fields['emptycartitem_type'] = intval($data['emptycartitem_type']);
		$assign_fields['emptycartitem_html'] = $data['emptycartitem_html'];
		$assign_fields['emptycartitem_url'] = $data['emptycartitem_url'];
		$assign_fields['emptycartitem_link_newtab'] = $data['emptycartitem_link_newtab'];
		$assign_fields['emptycartitem_priority'] = intval($data['emptycartitem_priority']);
		if(isset($data['emptycartitem_image_path']) && $data['emptycartitem_image_path'] != ''){
			$assign_fields['emptycartitem_image_path'] = $data['emptycartitem_image_path'];
		}
		if($emptycartitem_id === 0 && !isset($data['emptycartitem_status'])){
			$assign_fields['emptycartitem_status'] = 1;
		}if($emptycartitem_id > 0 && isset($data['emptycartitem_status'])){
			$assign_fields['emptycartitem_status'] = intval($data['emptycartitem_status']);
		}
		$record->assignValues($assign_fields);
		if($emptycartitem_id === 0 && $record->addNew()){
			$this->emptycartitem_id=$record->getId();
		}elseif($emptycartitem_id > 0 && $record->update(array('smt'=>'emptycartitem_id=?', 'vals'=>array($emptycartitem_id)))){
			$this->emptycartitem_id=$emptycartitem_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->emptycartitem_id;
	}
	
	function updateStatus($emptycartitem_id,$data_update=array()) {
		$emptycartitem_id = intval($emptycartitem_id);
		if($emptycartitem_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_empty_cart_items', $data_update, array('smt'=>'`emptycartitem_id` = ?', 'vals'=> array($emptycartitem_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($emptycartitem_id){
		$emptycartitem_id = intval($emptycartitem_id);
		if($emptycartitem_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		
		if($this->db->update_from_array('tbl_empty_cart_items', array('emptycartitem_is_deleted' => 1), array('smt' => 'emptycartitem_id = ?', 'vals' => array($emptycartitem_id)))){
			return true;
		}
		
		$this->error = $this->db->getError();
		return false;
	}
    
	
}