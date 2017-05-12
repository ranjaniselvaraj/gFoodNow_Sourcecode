<?php
class Blogposts extends Model {
    protected $db;
    protected $total_pages;
    protected $total_records;
    function __construct() {
        $this->db = Syspage::getdb();
    }
    function getAllCategories() {
        $srch = new SearchBase('tbl_blog_post_categories');
        $srch->addCondition('category_status', '=', 1);
        $srch->addOrder('category_title', 'ASC');
        $srch->addMultipleFields(array('`category_id`', '`category_title`', '`category_parent`'));
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        if ($this->total_records < 1) {
            return false;
        }
        return $this->db->fetch_all($rs,'category_id');
    }
    
    function addUpdate($data) {
        $post_id = intval($data['post_id']);
        unset($data['post_id']);
        $record = new TableRecord('tbl_blog_post');
        $assign_fields = array();
        $assign_fields['post_title'] = $data['post_title'];
        $assign_fields['post_short_description'] = $data['post_short_description'];
        $assign_fields['post_contributor_name'] = $data['post_contributor_name'];
        $assign_fields['post_content'] = $data['post_content'];
        $assign_fields['post_seo_name'] = $data['post_seo_name'];
        $assign_fields['post_status'] = $data['post_status'];
        $assign_fields['post_comment_status'] = $data['post_comment_status'];
        
        if($post_id==0){
        $assign_fields['post_date_time'] = 'mysql_func_now()';
        }
        if ($data['post_status'] == 1)
            $assign_fields['post_published'] = 'mysql_func_now()';
        $record->assignValues($assign_fields);
        $record_meta = new TableRecord('tbl_blog_meta_data');
        $assign_fields_meta = array();
        $assign_fields_meta['meta_title'] = $data['meta_title'];
        $assign_fields_meta['meta_keywords'] = $data['meta_keywords'];
        $assign_fields_meta['meta_description'] = $data['meta_description'];
        $assign_fields_meta['meta_others'] = $data['meta_others'];
        $assign_fields_meta['meta_record_type'] = 0;
        if ($post_id > 0) {
            $srch = new SearchBase('tbl_blog_post_images');
            $srch->addCondition('post_image_post_id', '=', $post_id);
            $srch->addCondition('post_image_default', '=', 1);
            $srch->addFld(array('post_image_default'));
            $rs = $srch->getResultSet();
            $this->total_records = $srch->recordCount();
            $this->total_pages = $srch->pages();
            if ($this->total_records < 1) {
                $value = 1;
            } else {
                $value = 0;
            }
            if (!empty($data['post_image_file_name'])) {
                $sql_image = 'INSERT INTO `tbl_blog_post_images` (`post_image_post_id`, `post_image_file_name`, `post_image_default`) VALUES';
                foreach ($data['post_image_file_name'] as $post_image) {
                    $sql_image .='(' . $post_id . ',"' . $post_image . '", ' . $value . '),';
                    $value = 0;
                }
                $sql_image = rtrim($sql_image, ',');
            } else {
                $sql_image = 'UPDATE `tbl_blog_post_images` set `post_image_default` = "1" where `post_image_post_id` = "' . $post_id . '" limit 1';
            }
            if (!empty($data['relation_category_id'])) {
                $sql = 'INSERT INTO `tbl_blog_post_category_relation` (`relation_post_id`, `relation_category_id`) VALUES ';
                foreach ($data['relation_category_id'] as $cat_id) {
                    $sql .='(' . $post_id . ',' . $cat_id . '),';
                }
                $sql = rtrim($sql, ',');
            }
        }
        $success = false;
        if ($post_id === 0 && $record->addNew()) {
            $post_id = intval($record->getId());
            if (!empty($data['relation_category_id'])) {
                $sql = 'INSERT INTO `tbl_blog_post_category_relation` (`relation_post_id`, `relation_category_id`) VALUES ';
                foreach ($data['relation_category_id'] as $cat_id) {
                    $sql .='(' . $post_id . ',' . $cat_id . '),';
                }
                $sql = rtrim($sql, ',');
            }
            if (!empty($data['post_image_file_name'])) {
                $value = ',"1"';
                $sql_image = 'INSERT INTO `tbl_blog_post_images` (`post_image_post_id`, `post_image_file_name`,`post_image_default`) VALUES ';
                foreach ($data['post_image_file_name'] as $post_image) {
                    $sql_image .='(' . $post_id . ',"' . $post_image . '"' . $value . '),';
                    $value = ', 0';
                }
                $sql_image = rtrim($sql_image, ',');
            }
            $assign_fields_meta['meta_record_id'] = $post_id;
            $record_meta->assignValues($assign_fields_meta);
            if ($record_meta->addNew()) {
                if (!empty($sql_image)) {
                    if ($this->db->query($sql_image)) {
                        
                    } else {
                        $this->error = $this->db->getError();
                        return false;
                    }
                }
                if (!empty($sql)) {
                    if ($this->db->query($sql)) {
                        return true;
                    } else {
                        $this->error = $this->db->getError();
                        return false;
                    }
                }
                return true;
            } else {
                echo $this->error = $this->db->getError();
                return false;
            }
        } elseif ($post_id > 0 && $record->update(array('smt' => 'post_id=?', 'vals' => array($post_id)))) {
            $assign_fields_meta['meta_record_id'] = $post_id;
            $record_meta->assignValues($assign_fields_meta);
            if ($record_meta->update(array('smt' => 'meta_id=?', 'vals' => array($data['meta_id'])))) {
                if (!empty($sql_image)) {
                    if ($this->db->query($sql_image)) {
                        
                    } else {
                        $this->error = $this->db->getError();
                        return false;
                    }
                }
                if (!empty($sql)) {
                    $this->db->deleteRecords('tbl_blog_post_category_relation', array('smt' => 'relation_post_id=?', 'vals' => array(intval($post_id))));
                    if ($this->db->query($sql)) {
                        //return true;
                    } else {
                        $this->error = $this->db->getError();
                        return false;
                    }
                }
                if (isset($data['remove_post_images']) && is_array($data['remove_post_images']) && sizeof($data['remove_post_images']) > 0) {
                    $rem_img_ids = array_map('intval', array_keys($data['remove_post_images']));
                    if (is_array($rem_img_ids) && sizeof($rem_img_ids) > 0) {
                        $rem_img_ids = implode(',', $rem_img_ids);
                        if (strlen($rem_img_ids) > 0 && $this->db->query('DELETE FROM `tbl_blog_post_images` WHERE `post_image_post_id` = ' . $post_id . ' AND `post_image_id` IN (' . $rem_img_ids . ')') && $this->db->rows_affected() == sizeof($data['remove_post_images'])) {
                            foreach ($data['remove_post_images'] as $img) {
                                Utilities::deleteFile($img, 'post-images/');
                            }
                        }
                    }
                }
                return true;
            } else {
                $this->error = $this->db->getError();
                return false;
            }
            return true;
        } else {
            $this->error = $this->db->getError();
            return false;
        }
        $this->error = 'Invalid request!!';
        return false;
    }
    function getBlogpostsData($data) {
        $srch = new SearchBase('tbl_blog_post', 'bp');
        if (isset($data['post_title']) && $data['post_title'] != "") {
            $srch->addCondition('post_title', 'like', '%' . $data['post_title'] . '%');
        }
        if (isset($data['post_status']) && $data['post_status'] != "") {
            $srch->addCondition('post_status', '=', $data['post_status']);
        }
        $srch->joinTable('tbl_blog_post_category_relation', 'INNER JOIN', 'bpcr.`relation_post_id` = bp.`post_id`', 'bpcr');
        $srch->joinTable('tbl_blog_post_categories', 'LEFT JOIN', 'bpc.`category_id` = bpcr.`relation_category_id`', 'bpc');
        $srch->setPageNumber($data['page']);
        $srch->setPageSize($data['pagesize']);
        $srch->addOrder('post_id', 'DESC');
        $srch->addGroupBy('post_id');
        $srch->addMultipleFields(array('bp.*', 'bpcr.`relation_post_id`', 'bpcr.`relation_category_id`','group_concat( DISTINCT (
bpc.`category_title`
)
SEPARATOR "," ) AS category_name'));
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        if ($this->total_records < 1) {
            return false;
        }
        return $this->db->fetch_all($rs);
    }
    function getBlogComments($data) {
        $srch = new SearchBase('tbl_blog_post_comments');
        if (isset($data['comment_author_name']) && $data['comment_author_name'] != "") {
            $srch->addCondition('comment_author_name', 'like', '%' . $data['comment_author_name'] . '%');
        }
        if (isset($data['comment_status']) && $data['comment_status'] != "") {
            $srch->addCondition('comment_status', '=', $data['comment_status']);
        }
        $srch->setPageNumber($data['page']);
        $srch->setPageSize($data['pagesize']);
        $srch->addOrder('comment_author_name', 'ASC');
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        if ($this->total_records < 1) {
            return false;
        }
        return $this->db->fetch_all($rs);
    }
    function getBlogPost($blog_post_id) {
        $blog_post_id = intval($blog_post_id);
        if ($blog_post_id < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_blog_post', 'bp');
        $srch->addCondition('post_id', '=', $blog_post_id);
        $srch->joinTable('tbl_blog_meta_data', 'INNER JOIN', 'bm.`meta_record_id` = bp.`post_id`', 'bm');
        $srch->joinTable('tbl_blog_post_category_relation', 'INNER JOIN', 'bp.`post_id` = bpcr.`relation_post_id`', 'bpcr');
        $srch->addCondition('bm.`meta_record_type`', '=', 0);
        $srch->addMultipleFields(array('bp.*', 'bm.*', 'GROUP_CONCAT(bpcr.`relation_category_id`) as relation_category_ids'));
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        return $this->db->fetch($rs);
    }
    function getTotalPages() {
        return $this->total_pages;
    }
    function getTotalRecords() {
        return $this->total_records;
    }
    function setMainImage($img_id, $blog_post_id) {
        $img_id = intval($img_id);
        $blog_post_id = intval($blog_post_id);
        if ($img_id < 1 || $blog_post_id < 1) {
            return false;
        }
        if ($this->db->startTransaction() && $this->db->update_from_array('tbl_blog_post_images', array('post_image_default' => 0), array('smt' => '`post_image_post_id`=? AND `post_image_default`=1', 'vals' => array($blog_post_id))) && $this->db->update_from_array('tbl_blog_post_images', array('post_image_default' => 1), array('smt' => '`post_image_id`=? AND `post_image_post_id`=?', 'vals' => array($img_id, $blog_post_id))) && $this->db->rows_affected() == 1 && $this->db->commitTransaction()) {
            return true;
        }
        $this->db->rollbackTransaction();
        return false;
    }
    function getPostImages($blog_post_id) {
        $blog_post_id = intval($blog_post_id);
        if ($blog_post_id < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_blog_post_images');
        $srch->addCondition('post_image_post_id', '=', $blog_post_id);
        $srch->addMultipleFields(array('`post_image_id`', '`post_image_file_name`', '`post_image_default`'));
        $rs = $srch->getResultSet();
        $pimgs = array();
        while ($row = $this->db->fetch($rs)) {
            $pimgs['imgs'][$row['post_image_id']] = $row['post_image_file_name'];
            if ($row['post_image_default'] == 1) {
                $pimgs['main_img'] = $row['post_image_id'];
            }
        }
        return $pimgs;
    }
    function deletePost($post_id, $relation_category_id = 0) {
        $post_id = intval($post_id);
        //$relation_category_id = intval($relation_category_id);
        if ($post_id < 1) {
            $this->error = 'Invalid request!!';
            return false;
        }
        if ($this->db->deleteRecords('tbl_blog_post', array('smt' => 'post_id=?', 'vals' => array($post_id)), array(), '', 1) && $this->db->deleteRecords('tbl_blog_post_category_relation', array('smt' => '`relation_post_id`=? ', 'vals' => array($post_id))) && $this->db->deleteRecords('tbl_blog_post_comments', array('smt' => '`comment_post_id`=? ', 'vals' => array($post_id)))) {
            if ($this->db->deleteRecords('tbl_blog_post_images', array('smt' => '`post_image_post_id`=? ', 'vals' => array($post_id)))) {
                return true;
            }
        }
        $this->error = $this->db->getError();
        return false;
    }
}
