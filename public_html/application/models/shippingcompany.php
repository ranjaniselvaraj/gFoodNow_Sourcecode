<?php
class Shippingcompany extends Model {
	
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
	
	function getShippingcompanies($criteria=array()) {
		$add_criteria = array();
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('scompany_id','desc');
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
    
    function search($criteria, $count='') {
        $srch = new SearchBase('tbl_shipping_companies', 'tsc');
		$srch->addCondition('tsc.scompany_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tsc.scompany_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tsc.scompany_name', 'like', '%'.$val.'%');
                break;	
			case 'pagesize':
                $srch->setPageSize($val);
                break;		
            }
        }
        return $srch;
    }
    
	function getAssociativeArray($not_to_include=null){
		$srch = new SearchBase('tbl_shipping_companies', 'tsc');
		$srch->addCondition('scompany_is_deleted', '=',0);
		if (!empty($not_to_include)) 
			$srch->addCondition('scompany_id', 'NOT IN',$not_to_include);
		$srch->addMultipleFields(array('scompany_id', 'scompany_name'));
		$srch->addOrder('scompany_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function addUpdate($data){
		$scompany_id = intval($data['scompany_id']);
		$record = new TableRecord('tbl_shipping_companies');
		$assign_fields = array();
		$assign_fields['scompany_name'] = $data['scompany_name'];
		$assign_fields['scompany_website'] = $data['scompany_website'];
		$assign_fields['scompany_comments'] = $data['scompany_comments'];
		$record->assignValues($assign_fields);
		if($scompany_id === 0 && $record->addNew()){
			$this->scompany_id=$record->getId();
		}elseif($scompany_id > 0 && $record->update(array('smt'=>'scompany_id=?', 'vals'=>array($scompany_id)))){
			$this->scompany_id=$scompany_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->scompany_id;
	}
	
	function delete($scompany_id){
		$scompany_id = intval($scompany_id);
		if($scompany_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_shipping_companies', array('scompany_is_deleted' => 1), array('smt' => 'scompany_id = ?', 'vals' => array($scompany_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
   
}