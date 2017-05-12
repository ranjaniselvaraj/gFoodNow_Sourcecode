<?php
class Slides extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
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
	
	function getAllSlides($criteria=array()) {
		$add_criteria = array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('ts.slide_status', 'desc');
		$rs = $srch->getResultSet();
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_slides', 'ts');
		$srch->addCondition('ts.slide_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('ts.slide_id', '=', intval($val));
                break;
			case 'position':
                $srch->addCondition('ts.slide_position', '=', $val);
                break;	
			case 'status':
	            $srch->addCondition('ts.slide_status', '=', intval($val));
                break;	
			case 'limit':
                $srch->setPageSize($val);
                break;			
            }
        }
        return $srch;
    }
	
	function addUpdate($data){
		$slide_id = intval($data['slide_id']);
		$record = new TableRecord('tbl_slides');
		$assign_fields = array();
		$assign_fields['slide_title'] = $data['slide_title'];
		$assign_fields['slide_url'] = $data['slide_url'];
		if(isset($data['slide_image_path']) && $data['slide_image_path'] != ''){
			$assign_fields['slide_image_path'] = $data['slide_image_path'];
		}
		$assign_fields['slide_link_newtab'] = $data['slide_link_newtab'];
		if($slide_id === 0 && !isset($data['slide_status'])){
			$assign_fields['slide_status'] = 1;
		}if($slide_id > 0 && isset($data['slide_status'])){
			$assign_fields['slide_status'] = intval($data['slide_status']);
		}
		$record->assignValues($assign_fields);
		if($slide_id === 0 && $record->addNew()){
			$this->slide_id=$record->getId();
		}elseif($slide_id > 0 && $record->update(array('smt'=>'slide_id=?', 'vals'=>array($slide_id)))){
			$this->slide_id=$slide_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->slide_id;
	}
    
	function updateSlideStatus($slide_id,$data_update=array()) {
		$slide_id = intval($slide_id);
		if($slide_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_slides', $data_update, array('smt'=>'`slide_id` = ?', 'vals'=> array($slide_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($slide_id){
		$slide_id = intval($slide_id);
		if($slide_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_slides', array('slide_is_deleted' => 1), array('smt' => 'slide_id = ?', 'vals' => array($slide_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}