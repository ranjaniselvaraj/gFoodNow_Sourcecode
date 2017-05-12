<?php
class Reportreasons extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT reportreason_id, reportreason_title FROM tbl_report_reasons WHERE 1=1 ";
        if (!empty($not_to_include)) $query .= " AND reportreason_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY reportreason_title";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
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
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_report_reasons', 'trr');
        if($count==true) {
            $srch->addFld('COUNT(trr.reportreason_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('trr.*'));
        }
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('trr.reportreason_id', '=', intval($val));
                break;
			case 'keyword':
				$srch->addDirectCondition('(trr.reportreason_title LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR trr.reportreason_description like '. $this->db->quoteVariable('%' . $val . '%') .')');
                break;	
            
            }
        }
        $srch->addOrder('trr.reportreason_id', 'desc');
        return $srch;
    }
	
	function getAllReportReasons($criteria=array()){
		$add_criteria = array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$rs = $srch->getResultSet();
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
	}
	
	function addUpdate($data){
		$reportreason_id = intval($data['reportreason_id']);
		$record = new TableRecord('tbl_report_reasons');
		$assign_fields = array();
		$assign_fields['reportreason_title'] = $data['reportreason_title'];
		$assign_fields['reportreason_description'] = $data['reportreason_description'];
		$record->assignValues($assign_fields);
		if($reportreason_id === 0 && $record->addNew()){
			$this->reportreason_id=$record->getId();
		}elseif($reportreason_id > 0 && $record->update(array('smt'=>'reportreason_id=?', 'vals'=>array($reportreason_id)))){
			$this->reportreason_id=$reportreason_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->reportreason_id;
	}
	
	function delete($reportreason_id){
		$reportreason_id = intval($reportreason_id);
		if($reportreason_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_report_reasons', array('smt'=>'reportreason_id=?', 'vals'=>array($reportreason_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
   
}