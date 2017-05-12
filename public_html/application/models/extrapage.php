<?php
class Extrapage extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getError() {
        return $this->error;
    }
	
    function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0==true)
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getExtraBlockData($add_criteria=array()) {
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_extra_pages', 'tep');
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tep.epage_id', '=', intval($val));
                break;
            case 'identifier':
                $srch->addCondition('tep.epage_identifier', '=', ($val));
                break;
            }
        }
        $srch->addOrder('tep.epage_id', 'ASC');
        return $srch;
    }
    
	function getExtraCmsPages($criteria=array()){
        $srch = new SearchBase('tbl_extra_pages', 'tep');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
				case 'id':
					$srch->addCondition('tep.epage_id', '=', intval($val));
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
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
	}
	
	function addUpdateExtraPage($data){
		$epage_id = intval($data['epage_id']);
		$record = new TableRecord('tbl_extra_pages');
		$assign_fields = array();
		$assign_fields['epage_label'] = $data['epage_label'];
		$assign_fields['epage_content'] = $data["epage_content"];
		$record->assignValues($assign_fields);
		if($epage_id === 0 && $record->addNew()){
			return $record->getId();
		}elseif($epage_id > 0 && $record->update(array('smt'=>'epage_id=?', 'vals'=>array($epage_id)))){
			return $epage_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function delete($epage_id){
		$epage_id = intval($epage_id);
		if($epage_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_extra_pages', array('smt'=>'epage_id=?', 'vals'=>array($epage_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
  
}