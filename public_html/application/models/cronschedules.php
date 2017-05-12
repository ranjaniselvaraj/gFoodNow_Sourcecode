<?php
class Cronschedules extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCronSchedules($sts=0) {
		$srch = new SearchBase('tbl_cron_schedules', 'tcs');
		if ($sts>0)
		$srch->addCondition('tcs.cron_active', '=',(int)$sts);
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		return $this->db->fetch_all($rs);
	}
	
    function getCronSchedule($id) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$srch = new SearchBase('tbl_cron_schedules', 'tcs');
		$srch->addCondition('tcs.cron_id', '=', $id);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getCronLastLoggedActivity($cron_id) {
        $cron_id = intval($cron_id);
        if($cron_id>0!=true) return array();
       	$srch = new SearchBase('tbl_cron_log', 'tcl');
		$srch->addCondition('tcl.cronlog_cron_id', '=', $cron_id);
		$srch->addOrder('cronlog_id','desc');
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function addUpdateCronLog($data){
		$cronlog_id = intval($data['cronlog_id']);
		$record = new TableRecord('tbl_cron_log');
		$assign_fields = array();
		$assign_fields['cronlog_cron_id'] = $data['cron_id'];
		$assign_fields['cronlog_details'] = $data['details'];
		if($cronlog_id === 0 && !isset($data['started_at'])){
			$assign_fields['cronlog_started_at'] = date("Y-m-d H:i:s");
		}if($cronlog_id > 0 && isset($data['ended_at'])){
			$assign_fields['cronlog_ended_at'] = $data['ended_at'];
		}
		$record->assignValues($assign_fields);
		if($cronlog_id === 0 && $record->addNew()){
			$this->cronlog_id=$record->getId();
		}elseif($cronlog_id > 0 && $record->update(array('smt'=>'cronlog_id=?', 'vals'=>array($cronlog_id)))){
			$this->cronlog_id=$cronlog_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->cronlog_id;
	}
	
	
}