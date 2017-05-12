<?php
class Cms extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getPageId() {
        return $this->page_id;
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
        $srch = new SearchBase('tbl_content_pages', 'tcp');
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tcp.page_id = REPLACE(tua.url_alias_query,"cms_id=","")', 'tua');
        $srch->addCondition('tcp.page_id', '=', $id);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
		$srch->addFld('tcp.*,tua.url_alias_keyword as seo_url_keyword');
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getCmsPages($criteria){
        $srch = new SearchBase('tbl_content_pages', 'tcp');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
				case 'id':
					$srch->addCondition('tcp.page_id', '=', intval($val));
					break;
				case 'pagesize':
					$srch->setPageSize($val);
					break;
				case 'page':
					$srch->setPageNumber($val);
					break;			
            }
        }
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
	}
	
	function addUpdatePage($data){
		$page_id = intval($data['page_id']);
		$record = new TableRecord('tbl_content_pages');
		$assign_fields = array();
		$assign_fields['page_title'] = $data['page_title'];
		$assign_fields['page_excerpt'] = $data['page_excerpt'];
		$assign_fields['page_content'] = $data["page_content"];
		$assign_fields['page_slug'] = $data['page_slug'];
		$assign_fields['page_meta_title'] = $data['page_meta_title'];
		$assign_fields['page_meta_keywords'] = $data['page_meta_keywords'];
		$assign_fields['page_meta_desc'] = $data['page_meta_desc'];
		if($page_id === 0){
			$assign_fields['page_added_on'] = date('Y-m-d H:i:s');
		}
		$record->assignValues($assign_fields);
		if($page_id === 0 && $record->addNew()){
			$this->page_id=$record->getId();
		}elseif($page_id > 0 && $record->update(array('smt'=>'page_id=?', 'vals'=>array($page_id)))){
			$this->page_id=$page_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		
		if (!$this->db->deleteRecords('tbl_url_alias', array('smt' => 'url_alias_query = ?', 'vals' => array('cms_id='.$this->page_id)))){
			$this->error = $this->db->getError();
			return false;
		}
	
		if (!empty($data['seo_url_keyword'])) {
			if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'cms_id='.$this->page_id,'url_alias_keyword'=>$data['seo_url_keyword']))){
				$this->error = $this->db->getError();
				return false;
			}
		}
		
		return $this->page_id;
	}
	
	function delete($page_id){
		$page_id = intval($page_id);
		if($page_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_content_pages', array('smt'=>'page_id=?', 'vals'=>array($page_id)))){
			$this->db->deleteRecords('tbl_url_alias', array('smt'=>'url_alias_query=? ', 'vals'=>array('cms_id='.$brand_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getAssociativeArray(){
		$srch = new SearchBase('tbl_content_pages', 'tcp');
		$srch->addMultipleFields(array('page_id', 'page_title'));
		$srch->addOrder('page_title', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
}