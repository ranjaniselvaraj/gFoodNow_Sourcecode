<?php
class Affiliatecommissions extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCommissionSettings($trashed=0) {
		$srch = new SearchBase('tbl_affiliate_commission_settings', 'tacs');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tacs.afcommsetting_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_affiliates', 'LEFT JOIN', 'tacs.afcommsetting_affiliate=ta.affiliate_id', 'ta');
		$srch->addCondition('tacs.afcommsetting_is_deleted', '=',$trashed);
		$srch->addMultipleFields(array('tacs.*','tc.category_name','CONCAT(ta.affiliate_name," [",ta.affiliate_username,"]") as affiliate'));
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function addUpdateCommissionSettings($data){
		if (isset($data['commission_setting'])) {
			foreach ($data['commission_setting'] as $key=>$val){
					$srch = new SearchBase('tbl_affiliate_commission_settings');
					$srch->addCondition('afcommsetting_affiliate', '=', $val["affiliate_id"]);
					$srch->addCondition('afcommsetting_category', '=', $val["category_id"]);
					$srch->addCondition('afcommsetting_is_deleted', '=',0);
					$srch->doNotCalculateRecords();
					$srch->doNotLimitRecords();
					$rs = $srch->getResultSet();
					if ($row = $this->db->fetch($rs)) {
						if ($row["afcommsetting_is_mandatory"]==1){
							$this->db->update_from_array('tbl_affiliate_commission_settings', array('afcommsetting_fees' => $val["fees"]), array('smt' => 'afcommsetting_id = ?', 'vals' => array($row['afcommsetting_id'])));
							continue;
						}
						else
						$this->db->update_from_array('tbl_affiliate_commission_settings', array('afcommsetting_is_deleted' => 1), array('smt' => 'afcommsetting_id = ?', 'vals' => array($row['afcommsetting_id'])));
					}
				
					$record = new TableRecord('tbl_affiliate_commission_settings');
					$arr=array("afcommsetting_id"=>(int)$val["id"],"afcommsetting_affiliate"=>$val["affiliate_id"],"afcommsetting_category"=>$val["category_id"],"afcommsetting_fees"=>$val["fees"],"afcommsetting_is_deleted"=>0,"afcommsetting_is_mandatory"=>(int)$val["mandatory"]);
					$record->assignValues($arr);
					if($record->addNew()){
						$inserted_record_id=$record->getId();
					}else{
						$this->error = $this->db->getError();
						return false;
					}
				
			}
		}
		return true;
	
		
	}
}