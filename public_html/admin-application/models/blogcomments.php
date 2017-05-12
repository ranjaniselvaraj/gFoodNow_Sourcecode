<?php
class Blogcomments extends Model {
	protected $db;
	protected $total_pages;
	protected $total_records;
	function __construct() {
		$this->db = Syspage::getdb();
    }
	
	function getBlogComments($data){
		$srch = new SearchBase('tbl_blog_post_comments', 'bpc');
		if(isset($data['comment_author_name']) && $data['comment_author_name'] != ""){
			$srch->addCondition('comment_author_name', 'like', '%' . $data['comment_author_name'] . '%');
		}
		if(isset($data['comment_status']) && $data['comment_status'] != ""){
			$srch->addCondition('comment_status', '=', $data['comment_status']);
		}
		$srch->joinTable('tbl_blog_post', 'INNER JOIN', 'bp.`post_id` = bpc.`comment_post_id`', 'bp');
		$srch->addMultipleFields(array('bpc.`comment_id`', 'bp.`post_id`', 'bp.`post_seo_name`', 'bpc.`comment_author_name`', 'bpc.`comment_author_email`', 'bpc.`comment_content`', 'bpc.`comment_status`', 'bp.`post_title`'));
		$srch->setPageNumber($data['page']);
		$srch->setPageSize($data['pagesize']);
		$srch->addOrder('comment_id', 'DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		return $this->db->fetch_all($rs);
	}
	
	function getComment($comment_id){
		$comment_id = intval($comment_id);
		if($comment_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_blog_post_comments');
		$srch->addCondition('comment_id', '=', $comment_id);
		$srch->doNotLimitRecords();
		$srch->doNotCalculateRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function updateStatus($data){
		$comment_id = intval($data['comment_id']);
		if($comment_id < 1){
			return false;
		}
		if($this->db->update_from_array('tbl_blog_post_comments', array('comment_status'=>$data['comment_status']), array('smt'=>'comment_id=?', 'vals'=>array(intval($comment_id))))){
			return true;
		}else{
			$this->error = $this->db->getError();
			return false;
		}	
	}
	
	function deleteComment($comment_id){
		$comment_id = intval($comment_id);
		if($comment_id < 0){
			$this->error = 'Invalid request!!';
			return false;
		}
		if($this->db->deleteRecords('tbl_blog_post_comments', array('smt'=>'comment_id=?', 'vals'=>array($comment_id)), array(), '', 1)){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}