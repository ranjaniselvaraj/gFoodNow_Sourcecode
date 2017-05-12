<?php
class Themes {
   
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
    function getThemeById($id, $add_criteria=array()) {
        $add_criteria['theme_id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getThemes($criteria=array()){
		$add_criteria =  array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('theme_display_order','asc');
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
		$srch = new SearchBase('tbl_themes', 'tm');
        foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
        switch($key) {
			case 'theme_id':
				$srch->addCondition('tm.theme_id', '=', ($val));
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
		$theme_id = intval($data['theme_id']);
		$record = new TableRecord('tbl_themes');
		$assign_fields = array();
		$assign_fields['theme_name'] = $data['theme_name'];
		$assign_fields['theme_display_order'] = $data['theme_display_order'];
		$assign_fields['theme_primary_color'] = $data['theme_primary_color'];
		$assign_fields['theme_secondary_color'] = $data['theme_secondary_color'];
		$assign_fields['theme_top_nav_text_color'] = $data['theme_top_nav_text_color'];
		$assign_fields['theme_top_nav_hover_color'] = $data['theme_top_nav_hover_color'];
		$assign_fields['theme_secondary_button_text_color'] = $data['theme_secondary_button_text_color'];
		$assign_fields['theme_top_bar_color'] = $data['theme_top_bar_color'];
		$assign_fields['theme_top_bar_text_color'] = $data['theme_top_bar_text_color'];
		$assign_fields['theme_left_box_color'] = $data['theme_left_box_color'];
		$assign_fields['theme_product_box_icon_price_color'] = $data['theme_product_box_icon_price_color'];
		if($theme_id === 0){
			$assign_fields['theme_added_by'] = $data['theme_added_by'];
		}
		$record->assignValues($assign_fields);
		if($theme_id === 0 && $record->addNew()){
			$this->theme_id=$record->getId();
		}elseif($theme_id > 0 && $record->update(array('smt'=>'theme_id=?', 'vals'=>array($theme_id)))){
			$this->theme_id=$theme_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->theme_id;
	}
	
	
	
	function delete($theme_id){
		$theme_id = intval($theme_id);
		if($theme_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_themes', array('smt' => 'theme_id = ? AND theme_added_by > ?', 'vals' => array($theme_id,0)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function createClone($theme_id,$added_by){
		$theme_id = intval($theme_id);
		if($theme_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$theme_details = $this->getThemeById($theme_id);
		unset($theme_details['theme_id']);
		$theme_details['theme_added_by']=$added_by;
		$theme_details['theme_name']="Clone of ".$theme_details['theme_name'];
		$this->addUpdate($theme_details);
		return true;
	}
	
}
?>