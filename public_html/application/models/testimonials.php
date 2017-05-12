<?php
class Testimonials {
   
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
    function getTestimonialById($id, $add_criteria=array()) {
        $add_criteria['testimonial_id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getTestimonials($criteria=array()){
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('testimonial_id','desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $testimonials=$this->db->fetch_all($rs);
		return $testimonials;
	}
   
    
    function search($criteria) {
		$srch = new SearchBase('tbl_testimonials', 'tm');
		$srch->addCondition('tm.testimonial_is_deleted', '=',0);
        foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
        switch($key) {
			case 'testimonial_id':
				$srch->addCondition('tm.testimonial_id', '=', ($val));
				break;
			case 'keyword':
					$srch->addDirectCondition('(tm.testimonial_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tm.testimonial_text like '. $this->db->quoteVariable('%' . $val . '%') .')');
			break;		
			case 'active':
				if (is_numeric($val))
				$srch->addCondition('tm.testimonial_status', '=', intval($val));
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
		$testimonial_id = intval($data['testimonial_id']);
		$record = new TableRecord('tbl_testimonials');
		$assign_fields = array();
		$assign_fields['testimonial_name'] = $data['testimonial_name'];
		$assign_fields['testimonial_address'] = $data['testimonial_address'];
		if(isset($data['testimonial_image']) && $data['testimonial_image'] != ''){
			$assign_fields['testimonial_image'] = $data['testimonial_image'];
		}
		$assign_fields['testimonial_text'] = $data['testimonial_text'];
		if($testimonial_id === 0 && !isset($data['testimonial_status'])){
			$assign_fields['testimonial_status'] = 1;
		}if($testimonial_id > 0 && isset($data['collection_status'])){
			$assign_fields['testimonial_status'] = intval($data['testimonial_status']);
		}
		$record->assignValues($assign_fields);
		if($testimonial_id === 0 && $record->addNew()){
			$this->testimonial_id=$record->getId();
		}elseif($testimonial_id > 0 && $record->update(array('smt'=>'testimonial_id=?', 'vals'=>array($testimonial_id)))){
			$this->testimonial_id=$testimonial_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->testimonial_id;
	}
	
	
	
	function delete($testimonial_id){
		$testimonial_id = intval($testimonial_id);
		if($testimonial_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_testimonials', array('testimonial_is_deleted' => 1), array('smt' => 'testimonial_id = ?', 'vals' => array($testimonial_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}
?>