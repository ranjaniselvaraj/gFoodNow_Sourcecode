<?php
class Shippingduration extends Model {
	
    function __construct(){
		$this->db = Syspage::getdb();
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
	
	function getShippingdurations($criteria=array()) {
		$add_criteria = array();
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('sduration_id','desc');
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_shipping_durations', 'tsd');
		$srch->addCondition('tsd.sduration_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tsd.sduration_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tsd.sduration_label', 'like', '%'.$val.'%');
                break;
			case 'pagesize':
                $srch->setPageSize($val);
                break;		
            
            }
        }
        return $srch;
    }
    
	function getAssociativeArray($not_to_include=null){
		$srch = new SearchBase('tbl_shipping_durations', 'tsd');
		$srch->addCondition('sduration_is_deleted', '=',0);
		if (!empty($not_to_include)) 
			$srch->addCondition('sduration_id', 'NOT IN',$not_to_include);
		$srch->addMultipleFields(array('sduration_id', 'sduration_label'));
		$srch->addOrder('sduration_label', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function addUpdate($data){
		$sduration_id = intval($data['sduration_id']);
		$record = new TableRecord('tbl_shipping_durations');
		$assign_fields = array();
		$assign_fields['sduration_label'] = $data['sduration_label'];
		$assign_fields['sduration_from'] = $data['sduration_from'];
		$assign_fields['sduration_to'] = $data['sduration_to'];
		$assign_fields['sduration_days_or_weeks'] = $data['sduration_days_or_weeks'];
		$record->assignValues($assign_fields);
		if($sduration_id === 0 && $record->addNew()){
			$this->sduration_id=$record->getId();
		}elseif($sduration_id > 0 && $record->update(array('smt'=>'sduration_id=?', 'vals'=>array($sduration_id)))){
			$this->sduration_id=$sduration_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->sduration_id;
	}
	
	function delete($sduration_id){
		$sduration_id = intval($sduration_id);
		if($sduration_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_shipping_durations', array('sduration_is_deleted' => 1), array('smt' => 'sduration_id = ?', 'vals' => array($sduration_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
   
}