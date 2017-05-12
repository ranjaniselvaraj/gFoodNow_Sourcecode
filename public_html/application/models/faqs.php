<?php
class Faqs extends Model {
	
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
	
	function getcategoryFaqs($category) {
        $category = intval($category);
        if($category>0!=true) return array();
       	$add_criteria['category'] = $category;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria) {
        $srch = new SearchBase('tbl_faqs', 'tf');
		$srch->joinTable('tbl_categories', 'INNER JOIN', 'tf.faq_category_id=tc.category_id and category_is_deleted=0', 'tc');
		$srch->addCondition('tf.faq_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tf.faq_id', '=', intval($val));
                break;
			case 'category':
                $srch->addCondition('tf.faq_category_id', '=', intval($val));
                break;	
			case 'keyword':
                $srch->addCondition('tf.faq_question_title', 'like', '%'.$val.'%');
                break;	
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;	
            
            }
        }
        $srch->addOrder('tf.faq_display_order', 'asc');
        return $srch;
    }
	
	function getFaqs($criteria) {
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
	
	function addUpdate($data){
		$faq_id = intval($data['faq_id']);
		$record = new TableRecord('tbl_faqs');
		$assign_fields = array();
		$assign_fields['faq_slug'] = $data['faq_slug'];
		$assign_fields['faq_category_id'] = intval($data['faq_category_id']);
		$assign_fields['faq_question_title'] = $data['faq_question_title'];
		$assign_fields['faq_answer_brief'] = $data['faq_answer_brief'];
		$assign_fields['faq_answer_detailed'] = $data['faq_answer_detailed'];
		$assign_fields['faq_meta_title'] = $data['faq_meta_title'];
		$assign_fields['faq_meta_keywords'] = $data['faq_meta_keywords'];
		$assign_fields['faq_meta_description'] = $data['faq_meta_description'];
		$assign_fields['faq_display_order'] = intval($data['faq_display_order']);
		if($faq_id === 0 && !isset($data['faq_status'])){
			$assign_fields['faq_status'] = 1;
		}if($faq_id > 0 && isset($data['faq_status'])){
			$assign_fields['faq_status'] = intval($data['faq_status']);
		}
		$record->assignValues($assign_fields);
		if($faq_id === 0 && $record->addNew()){
			$this->faq_id=$record->getId();
		}elseif($faq_id > 0 && $record->update(array('smt'=>'faq_id=?', 'vals'=>array($faq_id)))){
			$this->faq_id=$faq_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->faq_id;
	}
	
	function deleteFaq($faq_id){
		$faq_id = intval($faq_id);
		if($faq_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_faqs', array('smt'=>'faq_id=?', 'vals'=>array($faq_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
    
	
   
}