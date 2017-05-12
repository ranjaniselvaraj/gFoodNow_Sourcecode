<?php
class Emailtemplates extends Model {
    
	function __construct(){
		$this->db = Syspage::getdb();
    }
    
    function getData($id) {
        $srch = self::search(array('id'=>$id));
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    
   
    function search($criteria) {
        $srch = new SearchBase('tbl_email_templates', 'tem');
        foreach($criteria as $key=>$val) {
            switch($key) {
           		case 'id':
                	$srch->addCondition('tem.tpl_id', '=', intval($val));
                break;
	            case 'code':
    	            $srch->addCondition('tem.tpl_code', '=', $val);
                break;
            }
        }
        return $srch;
    }
	
	function addUpdate($data){
		$tpl_id = intval($data['tpl_id']);
		$record = new TableRecord('tbl_email_templates');
		$assign_fields = array();
		$assign_fields['tpl_name'] = $data['tpl_name'];
		$assign_fields['tpl_subject'] = $data['tpl_subject'];
		$assign_fields['tpl_body'] = $data['tpl_body'];
		$record->assignValues($assign_fields);
		if($tpl_id === 0 && $record->addNew()){
			$this->tpl_id=$record->getId();
		}elseif($tpl_id > 0 && $record->update(array('smt'=>'tpl_id=?', 'vals'=>array($tpl_id)))){
			$this->tpl_id=$tpl_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->tpl_id;
	}
	
	
	function updateEmailTemplateStatus($tpl_id,$data_update=array()) {
		$tpl_id = intval($tpl_id);
		if($tpl_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_email_templates', $data_update, array('smt'=>'`tpl_id` = ?', 'vals'=> array($tpl_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getAllEmailTemplates($criteria=array()) {
		$add_criteria = array();
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('tem.tpl_id', 'ASC');
		$srch->addOrder('tem.tpl_status', 'desc');
		$rs = $srch->getResultSet();
		$total_records = $srch->recordCount();
		if($total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
}