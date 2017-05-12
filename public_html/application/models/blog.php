<?php
class Blog extends Model {
	protected $db;
	private $total_pages;
	private $total_records;
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function addContributions($data){
      
		$record = new TableRecord('tbl_blog_contributions');
		$assign_fields = array();
		$assign_fields['contribution_author_first_name'] = $data['contribution_author_first_name'];
		$assign_fields['contribution_author_last_name'] = $data['contribution_author_last_name'];
		$assign_fields['contribution_author_email'] = $data['contribution_author_email'];
		$assign_fields['contribution_author_phone'] = $data['contribution_author_phone'];
		$assign_fields['contribution_file_name'] = $data['contribution_file_name'];
		$assign_fields['contribution_file_display_name'] = $data['contribution_file_display_name'];
		$assign_fields['contribution_status'] = 0;
		$assign_fields['contribution_date_time'] = 'mysql_func_now()';
		$assign_fields['contribution_user_id'] = $data['contribution_user_id'];
		$record->assignValues($assign_fields);
		if(!$record->addNew()){
			$this->error = $this->db->getError();
			return false;
		}
		$user_id = intval($record->getId());
		return $user_id;
	}
	
	function getArchives(){
		$srch = new SearchBase('tbl_blog_post');
		$srch->addFld(array('DATE_FORMAT(`post_published`, "%M-%Y") AS created_month'));
		$srch->addCondition('post_status', '=', 1);
		$srch->addGroupBy('created_month');
		$srch->addOrder('post_published', 'ASC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	function getAllCategories(){
		$srch = new SearchBase('tbl_blog_post_categories', 'bpc');
		$srch->addCondition('bpc.category_status', '=', 1);
		$srch->joinTable('tbl_blog_post_category_relation', 'LEFT OUTER JOIN', 'bpcr.`relation_category_id` = bpc.`category_id`', 'bpcr');
		$srch->joinTable('tbl_blog_post', 'LEFT OUTER JOIN', 'bp.`post_id` = bpcr.`relation_post_id` AND bp.`post_status` = 1', 'bp');
        $srch->addOrder('bpc.category_display_order', 'ASC');
		$srch->addOrder('bpc.category_title', 'ASC');
		
		$srch->addMultipleFields(array('bpc.`category_id`', 'count(bp.`post_id`) as count_post','bpc.`category_title`', 'bpc.`category_seo_name`', 'bpc.`category_parent`'));
		$srch->addGroupBy('bpc.category_id');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	function getRecentPost($data){
		$srch = new SearchBase('tbl_blog_post', 'bp');
		$srch->addCondition('bp.post_status', '=', 1);
		$srch->joinTable('tbl_blog_post_comments', 'LEFT OUTER JOIN', 'bpc.`comment_post_id` = bp.`post_id` AND bpc.`comment_status` = 1', 'bpc');
		$srch->setPageSize($data['pagesize']);
		$srch->addMultipleFields(array('bp.`post_id`', 'bp.`post_title`', 'bp.`post_seo_name`', 'bp.`post_comment_status`', 'bp.`post_published`', 'count(bpc.`comment_id`) as comment_count'));
		$srch->addOrder('bp.post_id', 'DESC');
		$srch->addGroupBy('bp.post_id');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	function getCategoryBySlug($cat_slug, $get_cat_code_only = false){
		if(!isset($cat_slug) || !is_string($cat_slug) || strlen($cat_slug) < 1){
			return false;
		}
		$srch = new SearchBase('tbl_blog_post_categories');
		$srch->addCondition('category_seo_name', '=', $cat_slug);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		if($get_cat_code_only === true){
			$srch->addFld('category_code');
		}
		$rs = $srch->getResultSet();
		$row = $this->db->fetch($rs);
		if(($get_cat_code_only === true) && $row){
			return $row['category_code'];
		}else{
			return $row;
		}
	}
	 
	private function getRootCategoryStatus($catId){
		if(intval($catId) < 1) return false;
		
		$srch = new SearchBase('tbl_blog_post_categories', 'bpc');
		$srch->addCondition('bpc.category_id', '=', $catId); 
		
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		$rs = $srch->getResultSet();
		$row = $this->db->fetch($rs);
		
		if($row['category_parent'] > 0) { 
			return $this->getRootCategoryStatus($row['category_parent']);
		 
		} else {
		 
			if($row['category_parent'] == 0 && $row['category_status'] != 1) {
				return false;
			}
		}
		return true;
	}
	 
	function getCatPosts($data){
		 
		if(!isset($data['cat_slug']) || !is_string($data['cat_slug']) || strlen($data['cat_slug']) < 1){
			return false;
		}
		
		$catData = $this->getCategoryBySlug($data['cat_slug']);
		$catId = $catData['category_id'];
		
		if(!$row = $this->getRootCategoryStatus($catId)){
			return false;
		}
		
		$srch = new SearchBase('tbl_blog_post_categories', 'bcat');
		$srch->joinTable('tbl_blog_post_category_relation', 'INNER JOIN', 'bpcr.`relation_category_id` = bcat.`category_id`', 'bpcr');
		$srch->joinTable('tbl_blog_post', 'INNER JOIN', '`post_id` = bpcr.`relation_post_id` AND bp.`post_status` = 1', 'bp');
		$srch->joinTable('tbl_blog_post_images', 'LEFT OUTER JOIN', 'bpimg.`post_image_post_id` = bp.`post_id` AND bpimg.`post_image_default` = 1', 'bpimg');
		$srch->joinTable('tbl_blog_post_images', 'LEFT OUTER JOIN', 'bpimg.`post_image_post_id` = bp.`post_id` AND bpimg.`post_image_default` = 1', 'bpimg');
		$srch->joinTable('tbl_blog_post_comments', 'LEFT OUTER JOIN', 'bpc.`comment_post_id` = bp.`post_id` AND bpc.`comment_status` = 1', 'bpc');
		//$srch->addCondition('category_code', 'like', $this->getCategoryBySlug($data['cat_slug'], true) . '%');
		$srch->addCondition('category_seo_name', '=', $data['cat_slug']);
		$srch->addCondition('bcat.category_status', '=', 1);
		$srch->setPageNumber($data['page']);
		$srch->setPageSize($data['pagesize']);
		$srch->addMultipleFields(array('bp.`post_id`', 'bp.`post_title`', 'bp.`post_view_count`', 'bp.post_contributor_name', 'bp.`post_short_description`', 'bp.`post_content`', 'bp.`post_seo_name`', 'count(DISTINCT bpc.`comment_id`) as comment_count',  'bp.`post_comment_status`', 'bp.`post_published`', 'bpimg.`post_image_file_name`'));
		$srch->addOrder('bp.post_id', 'DESC');
		$srch->addGroupBy('bp.post_id');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	function getSearchPost($data){
		$search = $data['search'];
		unset($data['search']);
		if($search == ''){
			$this->error = 'Invalid request!!';
			return false;
		}
		
		$srch = new SearchBase('tbl_blog_post', 'bp');
		$srch->addCondition('bp.post_status', '=', 1);
		$srch->addCondition('bp.post_title', 'like', '%' . $search . '%');
		$srch->joinTable('tbl_blog_post_images', 'LEFT OUTER JOIN', 'bpimg.`post_image_post_id` = bp.`post_id` AND bpimg.`post_image_default` = 1', 'bpimg');
		$srch->joinTable('tbl_blog_post_comments', 'LEFT OUTER JOIN', 'bpc.`comment_post_id` = bp.`post_id` AND bpc.`comment_status` = 1', 'bpc');
		 
		$srch->joinTable('tbl_blog_post_category_relation', 'LEFT OUTER JOIN', 'bpcr.`relation_post_id` = bp.`post_id`', 'bpcr');
		$srch->joinTable('tbl_blog_post_categories', 'LEFT OUTER JOIN', 'bpcat.`category_id` = bpcr.`relation_category_id`', 'bpcat');
		
		$srch->addCondition('bpcat.category_status', '=', 1);
		
		$srch->addOrder('bp.post_id', 'DESC');
		$srch->addGroupBy('bp.post_id');
		
		$srch->addMultipleFields(array('bp.`post_id`','bp.`post_view_count`', 'bp.`post_title`', 'bp.`post_contributor_name`', 'bp.`post_short_description`', 'bp.`post_content`', 'bp.`post_seo_name`', 'count(bpc.`comment_id`) as comment_count',  'bp.`post_comment_status`', 'bp.`post_published`', 'bpimg.`post_image_file_name`'));
		 
		$srch->setPageNumber($data['page']);
		$srch->setPageSize($data['pagesize']);
		
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	function getArchivesPost($data){
		$year = $data['year'];
		$month = $data['month'];
		if($year<1 || $month<1){
			$this->error = 'Invalid request!!';
			return false;
		}
		unset($data['year']);
		unset($data['month']);
		
		$srch = new SearchBase('tbl_blog_post', 'bp');
		$srch->addCondition('bp.post_status', '=', 1);
		$srch->addCondition('YEAR(bp.`post_published`)', '=', $year);
		$srch->addCondition('MONTH(bp.`post_published`)', '=', $month);
		$srch->joinTable('tbl_blog_post_images', 'LEFT OUTER JOIN', 'bpimg.`post_image_post_id` = bp.`post_id` AND bpimg.`post_image_default` = 1', 'bpimg');
		$srch->joinTable('tbl_blog_post_comments', 'LEFT OUTER JOIN', 'bpc.`comment_post_id` = bp.`post_id` AND bpc.`comment_status` = 1', 'bpc');
		$srch->setPageNumber($data['page']);
		$srch->setPageSize($data['pagesize']);
		$srch->addMultipleFields(array('bp.`post_id`', 'bp.`post_view_count`', 'bp.`post_title`', 'bp.`post_contributor_name`', 'bp.`post_short_description`', 'bp.`post_content`', 'bp.`post_seo_name`', 'count(bpc.`comment_id`) as comment_count',  'bp.`post_comment_status`', 'bp.`post_published`', 'bpimg.`post_image_file_name`'));
		$srch->addCondition('bpcat.category_status', '=', 1);
		$srch->joinTable('tbl_blog_post_category_relation', 'LEFT OUTER JOIN', 'bpcr.`relation_post_id` = bp.`post_id`', 'bpcr');
		$srch->joinTable('tbl_blog_post_categories', 'LEFT OUTER JOIN', 'bpcat.`category_id` = bpcr.`relation_category_id`', 'bpcat');
		$srch->addOrder('bp.`post_id`', 'DESC');
		$srch->addGroupBy('bp.`post_id`');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	private function getCatId($cat_slug){	
		$srch = new SearchBase('tbl_blog_post_categories');
		$srch->addFld('category_id');
		$srch->addCondition('category_seo_name', '=', $cat_slug);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getBlogPosts($data){
		$srch = new SearchBase('tbl_blog_post', 'bp');
		$srch->addCondition('bp.post_status', '=', 1);
		$srch->addCondition('bpcat.category_status', '=', 1);
		$srch->joinTable('tbl_blog_post_images', 'LEFT OUTER JOIN', 'bpimg.`post_image_post_id` = bp.`post_id` AND bpimg.`post_image_default` = 1', 'bpimg');
		$srch->joinTable('tbl_blog_post_comments', 'LEFT OUTER JOIN', 'bpc.`comment_post_id` = bp.`post_id` and bpc.`comment_status` = 1', 'bpc');
		
		$srch->joinTable('tbl_blog_post_category_relation', 'LEFT OUTER JOIN', 'bpcr.`relation_post_id` = bp.`post_id`', 'bpcr');
		
		$srch->joinTable('tbl_blog_post_categories', 'LEFT OUTER JOIN', 'bpcat.`category_id` = bpcr.`relation_category_id`', 'bpcat');
		
		$srch->setPageNumber($data['page']);
		$srch->setPageSize($data['pagesize']);
		$srch->addMultipleFields(array('bp.`post_id`', 'bp.`post_title`', 'bp.`post_contributor_name`', 'bp.`post_short_description`', 'bp.`post_content`', 'bp.`post_seo_name`', 'bp.`post_view_count`', 'count(DISTINCT bpc.`comment_id`) as comment_count',  'bp.`post_comment_status`', 'bp.`post_published`', 'bpimg.`post_image_file_name`'));
		$srch->addOrder('bp.post_id', 'DESC');
		$srch->addGroupBy('bp.post_id');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	function addComment($data){
		$record = new TableRecord('tbl_blog_post_comments');
		$assign_fields = array();
		$assign_fields['comment_post_id'] = $data['comment_post_id'];
		$assign_fields['comment_author_name'] = $data['comment_author_name'];
		$assign_fields['comment_author_email'] = $data['comment_author_email'];
		$assign_fields['comment_content'] = $data['comment_content'];
		$assign_fields['comment_date_time'] = 'mysql_func_now()';
		$assign_fields['comment_status'] = 0;
		$assign_fields['comment_ip'] = $_SERVER['REMOTE_ADDR'];
		$assign_fields['comment_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$assign_fields['comment_user_id'] = $data['comment_user_id'];
		$record->assignValues($assign_fields);
		if($record->addNew()){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getPostCommentCount($post_id){
		if(intval($post_id) < 1){
			$this->error = 'Invalid request!!';
			return false;
		}
		$srch = new SearchBase('tbl_blog_post_comments');
		$srch->addCondition('comment_post_id', '=', $post_id);
		$srch->addCondition('comment_status', '=', 1);
		$srch->addFld(array('count(`comment_post_id`) as comment_count'));
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		return $this->db->fetch_all($rs);
	}
	
	function getPostComments($data){
		$post_id = intval($data['post_id']);
		unset($data['post_id']);
		if($post_id < 1){
			$this->error = 'Invalid request!!';
			return false;
		}
		$srch = new SearchBase('tbl_blog_post_comments', 'bpc');
		$srch->addCondition('bpc.comment_post_id', '=', $post_id);
		$srch->addCondition('bpc.comment_status', '=', 1);
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'u.`user_id` = bpc.`comment_user_id`', 'u');
		
		$srch->joinTable('tbl_blog_attached_files', 'LEFT OUTER JOIN', 'u.`user_id` = f.`file_record_id` and file_type=4', 'f');
		
		$srch->addMultipleFields(array('bpc.`comment_author_name`', 'bpc.`comment_content`', 'bpc.`comment_date_time`', 'u.`user_id`', 'u.`user_profile_image`', 'f.`file_id`'));
		$srch->addOrder('bpc.comment_id', 'DESC');
		//$srch->addOrder('bpc.comment_id', 'DESC');
		$srch->setPageNumber($data['page']);
		$srch->setPageSize($data['pagesize']);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch_all($rs);
		}
		return false;
	}
	
	private function getPostId($post_slug){	
		$srch = new SearchBase('tbl_blog_post');
		$srch->addFld('post_id');
		$srch->addCondition('post_seo_name', '=', $post_slug);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch($rs);
		}
		$this->error = $this->db->getError();
		return false;
		
	}
	
	public function getPostById($id){	
		$id = intval($id);
		if($id<1){
			return false;
		}
		$srch = new SearchBase('tbl_blog_post'); 
		$srch->addCondition('post_id', '=', $id);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records >= 1){
			return $this->db->fetch($rs);
		}
		$this->error = $this->db->getError();
		return false;
		
	}
	
	function getPost($post_slug){
		if(!isset($post_slug) || !is_string($post_slug) || strlen($post_slug) < 1){
			return false;
		}
		$post_id_data = $this->getPostId($post_slug);
		if(empty($post_id_data)){
			return false;
		}
		$post_id = $post_id_data['post_id'];
		if(!intval($post_id) || empty($post_id)){
				return false;
		}
		$srch = new SearchBase('tbl_blog_post', 'bp');
		$srch->addCondition('bp.post_id', '=', $post_id);
		$srch->addCondition('bp.post_status', '=', 1);
		$srch->joinTable('tbl_blog_meta_data', 'INNER JOIN', 'bmd.`meta_record_id` = bp.`post_id` AND bmd.`meta_record_type` = 0 ', 'bmd');
		$srch->addMultipleFields(array('bp.*', 'bmd.`meta_title`', 'bmd.`meta_keywords`', 'bmd.`meta_description`', 'bmd.`meta_others`'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		$row = $this->db->fetch($rs);
		if($row){
			return $row;
		}
		return false;	
	}
	
	function getPostImages($post_slug){
		if(!isset($post_slug) || !is_string($post_slug) || strlen($post_slug) < 1){
			return false;
		}
		$post_id_data = $this->getPostId($post_slug);
		if(empty($post_id_data)){
			return false;
		}
		$post_id = $post_id_data['post_id'];
		if(!intval($post_id) || empty($post_id)){
				return false;
		}
		$srch = new SearchBase('tbl_blog_post_images');
		$srch->addCondition('post_image_post_id', '=', $post_id);
		$srch->addFld('post_image_file_name as slide_images');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		$row = $this->db->fetch_all($rs);
		if($row){
			return $row;
		}
		return false;	
		
	}
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function setPostViewsCount($post_id) {
		if(intval($post_id) < 1) return false;
		
		$srch = new SearchBase('tbl_blog_post');
		$srch->addCondition('post_id', '=', $post_id);
		$srch->addFld('post_view_count');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$result_data = $this->db->fetch($rs);
		$record = new TableRecord('tbl_blog_post');
		$assign_field = array();
		if (!empty($this->total_records)){
			$assign_field['post_view_count'] = $result_data['post_view_count']+1;
			$record->assignValues($assign_field);
			if($record->update(array('smt'=>'`post_id`=?', 'vals'=>array($post_id)))){
				return true;
			}
			$this->error = $this->db->getError();
			return false;
		}else{
			$assign_field['post_view_count'] = 1;
			$record->assignValues($assign_field);
			if($record->update(array('smt'=>'`post_id`=?', 'vals'=>array($post_id)))){
				return true;
			}
			else{
				$this->error = $this->db->getError();
				return false;
			}
		}
	}
	
	function getCategoryMetaDataByCatSlug($cat_slug){
		if(!isset($cat_slug) || !is_string($cat_slug) || strlen($cat_slug) < 1){
			return false;
		}
		$srch = new SearchBase('tbl_blog_meta_data', 'bmd');
		$srch->joinTable('tbl_blog_post_categories', 'INNER JOIN', 'bpc.`category_id` = bmd.`meta_record_id` AND bpc.`category_seo_name` = "'.$cat_slug.'" AND bmd.`meta_record_type` = 1 AND bpc.category_status = 1', 'bpc');
		$srch->addMultipleFields(array('bmd.`meta_title`', 'bmd.`meta_keywords`', 'bmd.`meta_description`', 'bmd.`meta_others`'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getPostCategories($data = array()){
		if( !isset($data['post_id']) || intval($data['post_id']) <= 0 ){
			return false;
		}
		$post_id = intval($data['post_id']);
		
		$srch = new SearchBase('tbl_blog_post_category_relation', 'relation');
		$srch->joinTable('tbl_blog_post_categories', 'INNER JOIN', 'bpc.category_id = relation.relation_category_id AND bpc.category_status = 1' ,'bpc');
		$srch->addMultipleFields( array('`bpc`.`category_id`', '`bpc`.`category_title`', '`bpc`.`category_seo_name`') );
		$srch->addCondition( 'relation.relation_post_id', '=', $post_id );
		$srch->addOrder( 'bpc.category_title');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
}