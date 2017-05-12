<?php
class Banners extends Model {
	
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
	
	function getAllBanners($criteria=array()) {
		$add_criteria = array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('tb.banner_status', 'desc');
		$rs = $srch->getResultSet();
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_banners', 'tb');
		$srch->addCondition('tb.banner_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tb.banner_id', '=', intval($val));
                break;
			case 'position':
                $srch->addCondition('tb.banner_position', '=', $val);
                break;	
			case 'status':
	            $srch->addCondition('tb.banner_status', '=', intval($val));
                break;	
			case 'limit':
                $srch->setPageSize($val);
                break;			
            }
        }
        return $srch;
    }
	
	function addUpdate($data){
		$banner_id = intval($data['banner_id']);
		$record = new TableRecord('tbl_banners');
		$assign_fields = array();
		$assign_fields['banner_title'] = $data['banner_title'];
		$assign_fields['banner_type'] = intval($data['banner_type']);
		$assign_fields['banner_html'] = $data['banner_html'];
		$assign_fields['banner_url'] = $data['banner_url'];
		$assign_fields['banner_link_newtab'] = $data['banner_link_newtab'];
		$assign_fields['banner_priority'] = intval($data['banner_priority']);
		if(isset($data['banner_image_path']) && $data['banner_image_path'] != ''){
			$assign_fields['banner_image_path'] = $data['banner_image_path'];
		}
		if($banner_id === 0 && !isset($data['banner_status'])){
			$assign_fields['banner_status'] = 1;
		}if($banner_id > 0 && isset($data['banner_status'])){
			$assign_fields['banner_status'] = intval($data['banner_status']);
		}
		$record->assignValues($assign_fields);
		if($banner_id === 0 && $record->addNew()){
			$this->banner_id=$record->getId();
		}elseif($banner_id > 0 && $record->update(array('smt'=>'banner_id=?', 'vals'=>array($banner_id)))){
			$this->banner_id=$banner_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->banner_id;
	}
	
	function updateBannerStatus($banner_id,$data_update=array()) {
		$banner_id = intval($banner_id);
		if($banner_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_banners', $data_update, array('smt'=>'`banner_id` = ?', 'vals'=> array($banner_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($banner_id){
		$banner_id = intval($banner_id);
		if($banner_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		
		if($this->db->update_from_array('tbl_banners', array('banner_is_deleted' => 1), array('smt' => 'banner_id = ?', 'vals' => array($banner_id)))){
			return true;
		}
		
		$this->error = $this->db->getError();
		return false;
	}
    
	
}