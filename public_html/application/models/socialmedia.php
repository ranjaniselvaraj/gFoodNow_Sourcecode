<?php
class Socialmedia {
   
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
    function getSocialMediaById($id, $add_criteria=array()) {
        $add_criteria['splatform_id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getSocialMedias($criteria=array()){
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('sm.splatform_status', 'desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $themes=$this->db->fetch_all($rs);
		return $themes;
	}
   
    
    function search($criteria) {
		$srch = new SearchBase('tbl_social_platforms', 'sm');
		$srch->addCondition('sm.splatform_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
        switch($key) {
			case 'splatform_id':
				$srch->addCondition('sm.splatform_id', '=', ($val));
				break;
			case 'status':
	            $srch->addCondition('sm.splatform_status', '=', intval($val));
                break;	
			case 'page':
					$srch->setPageNumber($val);
				break;	
			case 'pagesize':
					$srch->setPageSize($val);
			break;
            }
        }
        return $srch;
    }
	
	function addUpdate($data){
		$splatform_id = intval($data['splatform_id']);
		$record = new TableRecord('tbl_social_platforms');
		$assign_fields = array();
		$assign_fields['splatform_title'] = $data['splatform_title'];
		$assign_fields['splatform_url'] = $data['splatform_url'];
		if(isset($data['splatform_icon_file']) && $data['splatform_icon_file'] != ''){
			$assign_fields['splatform_icon_file'] = $data['splatform_icon_file'];
		}
		if($splatform_id === 0 && !isset($data['splatform_status'])){
			$assign_fields['splatform_status'] = 1;
		}if($splatform_id > 0 && isset($data['splatform_status'])){
			$assign_fields['splatform_status'] = intval($data['splatform_status']);
		}
		$record->assignValues($assign_fields);
		if($splatform_id === 0 && $record->addNew()){
			$this->splatform_id=$record->getId();
		}elseif($splatform_id > 0 && $record->update(array('smt'=>'splatform_id=?', 'vals'=>array($splatform_id)))){
			$this->splatform_id=$splatform_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->splatform_id;
	}
	
	function updateSocialPlatformStatus($splatform_id,$data_update=array()) {
		$splatform_id = intval($splatform_id);
		if($splatform_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_social_platforms', $data_update, array('smt'=>'`splatform_id` = ?', 'vals'=> array($splatform_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($splatform_id){
		$splatform_id = intval($splatform_id);
		if($splatform_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_social_platforms', array('splatform_is_deleted' => 1), array('smt' => 'splatform_id = ?', 'vals' => array($splatform_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}
?>