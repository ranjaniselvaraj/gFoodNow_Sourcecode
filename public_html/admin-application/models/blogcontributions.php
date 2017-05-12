<?php
class Blogcontributions extends Model {
    protected $db;
    protected $total_pages;
    protected $total_records;
    function __construct() {
        $this->db = Syspage::getdb();
    }
    function getContributionsData($data) {
        $srch = new SearchBase('tbl_blog_contributions');
        if (isset($data['contribution_author_first_name']) && $data['contribution_author_first_name'] != "") {
            $srch->addCondition('contribution_author_first_name', 'like', '%' . $data['contribution_author_first_name'] . '%');
        }
        if (isset($data['contribution_status']) && $data['contribution_status'] != "") {
            $srch->addCondition('contribution_status', '=', $data['contribution_status']);
        }
        $srch->setPageNumber($data['page']);
        $srch->setPageSize($data['pagesize']);
        $srch->addOrder('contribution_id', 'DESC');
        $rs = $srch->getResultSet();
        $this->total_records = $srch->recordCount();
        $this->total_pages = $srch->pages();
        if ($this->total_records < 1) {
            return false;
        }
        return $this->db->fetch_all($rs);
    }
    function getContributionPost($contribution_id) {
        $contribution_id = intval($contribution_id);
        if ($contribution_id < 1) {
            return false;
        }
        $srch = new SearchBase('tbl_blog_contributions');
        $srch->addCondition('contribution_id', '=', $contribution_id);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $rs = $srch->getResultSet();
        return $this->db->fetch($rs);
    }
    function updateStatus($data) {
        $contribution_id = intval($data['contribution_id']);
        if ($contribution_id < 1) {
            return false;
        }
        if ($this->db->update_from_array('tbl_blog_contributions', array('contribution_status' => $data['contribution_status']), array('smt' => 'contribution_id=?', 'vals' => array(intval($contribution_id))))) {
            return true;
        } else {
            $this->error = $this->db->getError();
            return false;
        }
    }
    function getTotalPages() {
        return $this->total_pages;
    }
    function getTotalRecords() {
        return $this->total_records;
    }
    function deleteContribution($contribution_id) {
        $contribution_id = intval($contribution_id);
        if ($contribution_id < 0) {
            $this->error = 'Invalid request!!';
            return false;
        }
        if ($this->db->deleteRecords('tbl_blog_contributions', array('smt' => 'contribution_id=?', 'vals' => array($contribution_id)), array(), '', 1)) {
            return true;
        }
        $this->error = $this->db->getError();
        return false;
    }
}
