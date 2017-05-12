<?php
class Navigations {
    function __construct(){
		$this->db = Syspage::getdb();
    }
	function getNavigationId() {
        return $this->navigation_id;
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
	
	function getnavigations($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addorder('nav_status','desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
    function getNavigationById($id, $add_criteria=array()) {
        $add_criteria['nav_id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
    }
	
	function getNavigationPageById($id, $add_criteria=array()) {
        $add_criteria['nl_id'] = $id;
        $srch = self::searchPages($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
         $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
    }
	
	function getNavigationPagesById($id, $add_criteria=array()) {
        $add_criteria['nav_id'] = $id;
        $srch = self::searchPages($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
		$srch->addOrder('nl_display_order');
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
    }
    
    function search($criteria) {
        $srch = new SearchBase('tbl_navigations', 'tnav');
		$srch->addCondition('tnav.nav_is_deleted', '=',0);
        foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
        switch($key) {
       		case 'nav_id':
	           	 $srch->addCondition('tnav.nav_id', '=', intval($val));
            break;
	       }
        }
        return $srch;
    }
	
	function searchPages($criteria) {
        $srch = new SearchBase('tbl_nav_links', 'tnl');
		$srch->addCondition('nl_is_deleted', '=', '0', $attachment_glue='', $execute_mysql_functions=false);
        foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
        switch($key) {
        case 'nav_id':
            $srch->addCondition('tnl.nl_nav_id', '=', intval($val));
            break;
		case 'nl_id':
            $srch->addCondition('tnl.nl_id', '=', intval($val));
            break;	
	        }
        }
        return $srch;
    }
	
	function addUpdateNavigation($data){
		$nav_id = intval($data['nav_id']);
		$record = new TableRecord('tbl_navigations');
		$assign_fields = array();
		$assign_fields['nav_name'] = $data['nav_name'];
		if($nav_id === 0 && !isset($data['nav_status'])){
			$assign_fields['nav_status'] = 1;
		}if($nav_id > 0 && isset($data['nav_status'])){
			$assign_fields['nav_status'] = intval($data['nav_status']);
		}
		$record->assignValues($assign_fields);
		if($nav_id === 0 && $record->addNew()){
			$this->nav_id=$record->getId();
		}elseif($nav_id > 0 && $record->update(array('smt'=>'nav_id=?', 'vals'=>array($nav_id)))){
			$this->nav_id=$nav_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->nav_id;
	}
	
	
	function addUpdatePage($data, $langs = array()){
		$nav_page_id = intval($data['nav_page_id']);
		$record = new TableRecord('tbl_nav_links');
		$assign_fields = array();
		$assign_fields['nl_caption'] = $data['nl_caption'];
		$assign_fields['nl_cms_page_id'] = $data['nl_cms_page_id'];
		$assign_fields['nl_html'] = $data['nl_type']==1?$data['custom_html']:$data["external_page"];
		$assign_fields['nl_target'] = $data['nl_target'];
		$assign_fields['nl_nav_id'] = intval($data['navigation_id']);
		$assign_fields['nl_type'] = intval($data['nl_type']);
		$assign_fields['nl_parent_id'] = intval($data['nl_parent_id']);
		$assign_fields['nl_code'] = $data['nl_code'];
		$assign_fields['nl_display_order'] = intval($data['nl_display_order']);
		$assign_fields['nl_login_protected'] = intval($data['nl_login_protected']);
		$record->assignValues($assign_fields);
		if($nav_page_id === 0 && $record->addNew()){
			return $record->getId();
		}elseif($nav_page_id > 0 && $record->update(array('smt'=>'nl_id=?', 'vals'=>array($nav_page_id)))){
			return $nav_page_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function updateNavigationStatus($navigation_id,$data_update=array()) {
		$navigation_id = intval($navigation_id);
		if($navigation_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_navigations', $data_update, array('smt'=>'`nav_id` = ?', 'vals'=> array($navigation_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function deletePage($nav_page_id){
		$nav_page_id = intval($nav_page_id);
		if($nav_page_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->deleteRecords('tbl_nav_links', array('smt'=>'nl_id=?', 'vals'=>array($nav_page_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
	
}