<?php
class Blogcategories extends Model {
    protected $db;
    protected $total_pages;
    protected $total_records;
    protected $catArrayname= array();
    function __construct() {
        $this->db = Syspage::getdb();
    }
    function getAllCategories($blog_category_id = '') {
        $srch = new SearchBase('tbl_blog_post_categories');
        $srch->addCondition('category_status', '=', 1);
        $srch->addCondition('category_parent', '=', 0);
        if (!empty($blog_category_id)) {
            $srch->addCondition('category_id', '!=', $blog_category_id);
        }
        $srch->addOrder('category_title', 'ASC');
        $srch->addMultipleFields(array('`category_id`', '`category_title`'));
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        if ($this->total_records < 1) {
            return false;
        }
        return $this->db->fetch_all_assoc($rs);
    }
    function fetchCatname($blog_category_id) {
        $srch = new SearchBase('tbl_blog_post_categories');
        if (!empty($blog_category_id)) {
            $srch->addCondition('category_id', '=', $blog_category_id);
        }
        $rs = $srch->getResultSet();
        $total_records = $srch->recordCount();
        if ($total_records < 1) {
            return false;
        }
        return $this->db->fetch($rs);
    }
    function categoryDisplayOrder($data, $catID) {
        $record = new TableRecord('tbl_blog_post_categories');
        foreach ($data['category'] as $key => $value) {
            if (!empty($value)) {
                $cat[$key]['category_id'] = $value;
                $cat[$key]['category_display_order'] = $key;
                $record->assignValues($cat[$key]);
                if(!($record->update(array('smt' => 'category_id=?and category_parent = ?', 'vals' => array($value,$catID))))){
                    $this->error = $this->db->getError();
                return false;
                }
            }
        }
    }
	
	function fetchCatChilds($blog_category_id){
		$srch = new SearchBase('tbl_blog_post_categories');
        if (!empty($blog_category_id)) {
            $srch->addCondition('category_parent', '=', $blog_category_id);
        }
        $rs = $srch->getResultSet();
        $total_records = $srch->recordCount();
        if ($total_records < 1) {
            return false;
        }
        return $this->db->fetch_all($rs);
	}
	
	function disableChildCats($category_id){
		$cats = $this->fetchCatChilds($category_id);
		
		foreach($cats as $key => $val){
			$record = new TableRecord('tbl_blog_post_categories');
			$assign_fields['category_status'] = 0;
			$record->assignValues($assign_fields);
			$record->update(array('smt' => 'category_id=?', 'vals' => array($val['category_id'])));
			
			$this->disableChildCats($val['category_id']);
		}
		
	}
    function addUpdate($data) {
        $category_id = intval($data['category_id']);
        unset($data['category_id']);
		if(intval($data['category_status']) == 0){
			$this->disableChildCats($category_id);
		}
        $record = new TableRecord('tbl_blog_post_categories');
        $assign_fields = array();
        $assign_fields['category_title'] = $data['category_title'];
        $assign_fields['category_description'] = $data['category_description'];
        $assign_fields['category_seo_name'] = $data['category_seo_name'];
        $assign_fields['category_status'] = $data['category_status'];
        $assign_fields['category_display_order'] = $data['category_display_order'];
        $assign_fields['category_date_time'] = 'mysql_func_now()';
        $assign_fields['category_parent'] = $data['category_parent'];
        if ($data['category_parent'] == '') {
            $assign_fields['category_parent'] = 0;
        }
        if ($category_id > 0) {
            if (!empty($data['category_parent'])) {
                $category_parent_code = $this->getCategoryCode($data['category_parent']);
                $category_parent_code = $category_parent_code['category_code'];
                $assign_fields['category_code'] = $category_parent_code . str_pad($category_id, 5, "0", STR_PAD_LEFT);
            } else {
                $assign_fields['category_code'] = str_pad($category_id, 5, "0", STR_PAD_LEFT);
            }
        }
        //printR($assign_fields);die;
        $record->assignValues($assign_fields);
        $record_meta = new TableRecord('tbl_blog_meta_data');
        $assign_fields_meta = array();
        $assign_fields_meta['meta_title'] = $data['meta_title'];
        $assign_fields_meta['meta_keywords'] = $data['meta_keywords'];
        $assign_fields_meta['meta_description'] = $data['meta_description'];
        $assign_fields_meta['meta_others'] = $data['meta_others'];
        $assign_fields_meta['meta_record_type'] = 1;
        $success = false;
        if ($category_id === 0 && $record->addNew()) {
            $category_id = intval($record->getId());
            if (empty($assign_fields['category_code'])) {
                $category_parent_code = $this->getCategoryCode($data['category_parent']);
                $category_parent_code = $category_parent_code['category_code'];
                $assign_fields_category_code['category_code'] = $category_parent_code . str_pad($category_id, 5, "0", STR_PAD_LEFT);
                //$assign_fields_category_code['category_code'] = str_pad($category_id, 5, "0", STR_PAD_LEFT);
            }
            $record->assignValues($assign_fields_category_code);
            $record->update(array('smt' => 'category_id=?', 'vals' => array($category_id)));
            $assign_fields_meta['meta_record_id'] = $category_id;
            $record_meta->assignValues($assign_fields_meta);
            if ($record_meta->addNew()) {
                return true;
            } else {
                $this->error = $this->db->getError();
                return false;
            }
        } elseif ($category_id > 0 && $record->update(array('smt' => 'category_id=?', 'vals' => array($category_id)))) {
            $assign_fields_meta['meta_record_id'] = $category_id;
            $record_meta->assignValues($assign_fields_meta);
            if ($record_meta->update(array('smt' => 'meta_id=?', 'vals' => array($data['meta_id'])))) {
                return true;
            } else {
                $this->error = $this->db->getError();
                return false;
            }
        } else {
            $this->error = $this->db->getError();
            return false;
        }
        $this->error = 'Invalid request!!';
        return false;
    }
	 
    private function getCategoryCode($category_id) {
        $category_id = intval($category_id);
        if ($category_id < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_blog_post_categories');
        $srch->addCondition('category_id', '=', $category_id);
        $srch->addFld('category_code');
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        if ($this->total_records >= 1) {
            return $this->db->fetch($rs);
        }
        return false;
    }
    function getBlogcategoriesData($data) {
        $srch = new SearchBase('tbl_blog_post_categories', 'bpc');
        if (isset($data['category_title']) && $data['category_title'] != "") {
            $srch->addCondition('bpc.category_title', 'like', '%' . $data['category_title'] . '%');
        }
        if (isset($data['category_status']) && $data['category_status'] != "") {
            $srch->addCondition('bpc.category_status', '=', $data['category_status']);
        }
        if (isset($data['category_parent']) && $data['category_parent'] != "") {
            $srch->addCondition('bpc.category_parent', '=', $data['category_parent']);
        }
        $srch->addMultipleFields(array('bpc.*', 'bpc1.`category_title` AS cat_parent', 'COUNT(bpc2.`category_id`) AS cat_level'));
        $srch->joinTable('tbl_blog_post_categories', 'LEFT OUTER JOIN', 'bpc1.`category_id` = bpc.category_parent', 'bpc1');
        $srch->joinTable('tbl_blog_post_categories', 'LEFT OUTER JOIN', 'bpc2.`category_code` LIKE CONCAT("%",bpc.category_code, "%")', 'bpc2');
        $srch->setPageNumber($data['page']);
        $srch->setPageSize($data['pagesize']);
        $srch->addOrder('bpc.category_status', 'DESC');
        $srch->addOrder('bpc.category_display_order', 'ASC');
        $srch->addGroupBy('bpc.category_id');
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        if ($this->total_records < 1) {
            return false;
        }
        return $this->db->fetch_all($rs);
    }
    function getBlogcategory($blog_category_id) {
        $blog_category_id = intval($blog_category_id);
        if ($blog_category_id < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_blog_post_categories', 'bp');
        $srch->addCondition('bp.category_id', '=', $blog_category_id);
        $srch->joinTable('tbl_blog_meta_data', 'LEFT OUTER JOIN', 'bm.`meta_record_id` = ' . $blog_category_id . '', 'bm');
        $srch->addCondition('bm.meta_record_type', '=', 1);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        //echo $srch->getQuery();
        $rs = $srch->getResultSet();
        return $this->db->fetch($rs);
    }
    function getTotalPages() {
        return $this->total_pages;
    }
    function getTotalRecords() {
        return $this->total_records;
    }
    function getlastDisplayOrder() {
        $srch = new SearchBase('tbl_blog_post_categories');
        $srch->addFld('max(category_display_order) as max');
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        //echo $srch->getQuery();die;
        $rs = $srch->getResultSet();
        $record = $this->db->fetch($rs);
        if ($record['max']) {
            return $record['max'] + 1;
        }
    }
    function deleteCategory($category_id) {
        $category_id = intval($category_id);
        if ($category_id < 0) {
            $this->error = 'Invalid request!!';
            return false;
        }
        if ($this->db->deleteRecords('tbl_blog_post_categories', array('smt' => 'category_id=?', 'vals' => array($category_id)), array(), '', 1)) {
            $this->db->deleteRecords('tbl_blog_post_category_relation', array('smt' => 'relation_category_id=?', 'vals' => array($category_id)), array(), '', 1);
            return true;
        }
        $this->error = $this->db->getError();
        return false;
    }
    function chkPostOfCategory($category_id) {
        $category_id = intval($category_id);
        if ($category_id < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_blog_post_category_relation');
        $srch->addCondition('relation_category_id', '=', $category_id);
        $srch->addFld('count(relation_post_id) as count_rows');
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        //echo $srch->getQuery();die;
        $rs = $srch->getResultSet();
        $record = $this->db->fetch($rs);
        if ($record['count_rows'] == 0) {
            return true;
        }
        return false;
    }
    
    function fetchbreadcrumbArray($catid){
        
       $catInfo = $this->fetchCatname($catid);
       $this->catArrayname[$catid]=$catInfo['category_title'];
       if($catInfo['category_parent']>0){
          $this->fetchbreadcrumbArray($catInfo['category_parent']);
       }
       return $this->catArrayname;
    }
}
