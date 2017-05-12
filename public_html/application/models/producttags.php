<?php
class Producttags extends Model {
	
    function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
    function getData($id) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tpt.ptag_id = REPLACE(tua.url_alias_query,"tags_id=","")', 'tua');
        $srch->addMultipleFields(array('DISTINCT tpt.*','tua.url_alias_keyword as seo_url_keyword'));
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getTagByUrlAlias($url_alias) {
        if(empty($url_alias)) return array();
        $srch = new SearchBase('tbl_product_tags', 'tpt');
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tpt.ptag_id = REPLACE(tua.url_alias_query,"tags_id=","")', 'tua');
		$srch->addCondition('tua.url_alias_keyword', '=', $url_alias);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getProductTags($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('ptag_name','asc');
		//die($srch->getquery());
        $rs = $srch->getResultSet();
		
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_product_tags', 'tpt');
		//$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tpt.ptag_id = REPLACE(tua.url_alias_query,"tags_id=","")', 'tua');
		//$srch->addFld('tua.url_alias_keyword as seo_url_keyword');
		
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tpt.ptag_id', '=', intval($val));
                break;
			case 'name':
                $srch->addCondition('tpt.ptag_name', '=', $val);
                break;
			case 'keyword':
                $srch->addCondition('tpt.ptag_name', 'like', '%'.$val.'%');
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
	function getProductTagByName($name) {
        if($name=="") return array();
       	$add_criteria['name'] = $name;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
	function getAssociativeArray($not_to_include=null){
		$srch = new SearchBase('tbl_product_tags', 'tpt');
		if (!empty($not_to_include)) 
			$srch->addCondition('ptag_id', 'NOT IN',$not_to_include);
		$srch->addMultipleFields(array('ptag_id', 'ptag_name'));
		$srch->addOrder('ptag_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function addUpdate($data){
		$ptag_id = intval($data['ptag_id']);
		$record = new TableRecord('tbl_product_tags');
		$assign_fields = array();
		$assign_fields['ptag_name'] = $data['ptag_name'];
		if($ptag_id === 0){
			$assign_fields['ptag_owner'] = $data['owner'];
			$assign_fields['ptag_added_by'] = intval($data['added_by']);
		}
		
		$record->assignValues($assign_fields);
		if($ptag_id === 0 && $record->addNew()){
			$this->ptag_id=$record->getId();
		}elseif($ptag_id > 0 && $record->update(array('smt'=>'ptag_id=?', 'vals'=>array($ptag_id)))){
			$this->ptag_id=$ptag_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		if (!$this->db->deleteRecords('tbl_url_alias', array('smt' => 'url_alias_query = ?', 'vals' => array('tags_id='.$this->ptag_id)))){
			$this->error = $this->db->getError();
			return false;
		}
	
		if (!empty($data['seo_url_keyword'])) {
			if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'tags_id='.$this->ptag_id,'url_alias_keyword'=>$data['seo_url_keyword']))){
				$this->error = $this->db->getError();
				return false;
			}
		}
		return $this->ptag_id;
	}
	
	function delete($ptag_id){
		$ptag_id = intval($ptag_id);
		if($ptag_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_product_tags', array('smt' => 'ptag_id = ?', 'vals' => array($ptag_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	public function recordTagWeightage($tag_id){
		$srObj=new SmartRecommendations();
		return $srObj->addUpdateUserBrowsingActivity($tag_id,4);
	}
	
   
}