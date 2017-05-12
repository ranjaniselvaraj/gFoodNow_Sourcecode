<?php
class Commissions extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getCommissionSettings($trashed=0) {
		$srch = new SearchBase('tbl_commission_settings', 'tcs');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tcs.commsetting_product=tp.prod_id', 'tp');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tcs.commsetting_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tcs.commsetting_vendor=tu.user_id', 'tu');
		$srch->addCondition('tcs.commsetting_is_deleted', '=',$trashed);
		$srch->addMultipleFields(array('tcs.*','tp.prod_name','tc.category_name','CONCAT(tu.user_name," [",tu.user_username,"]") as vendor'));
		//$srch->addOrder('tcs.sformfield_order', 'asc');
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function addUpdateCommissionSettings($data){
		if (isset($data['commission_setting'])) {
			foreach ($data['commission_setting'] as $key=>$val){
					if ($val["product_id"]>0){
						unset($val["vendor_id"]);
						unset($val["category_id"]);
					}
					$srch = new SearchBase('tbl_commission_settings');
					$srch->addCondition('commsetting_product', '=', $val["product_id"]);
					$srch->addCondition('commsetting_vendor', '=', $val["vendor_id"]);
					$srch->addCondition('commsetting_category', '=', $val["category_id"]);
					$srch->addCondition('commsetting_is_deleted', '=',0);
					$srch->doNotCalculateRecords();
					$srch->doNotLimitRecords();
					$rs = $srch->getResultSet();
					if ($row = $this->db->fetch($rs)) {
						if ($row["commsetting_is_mandatory"]==1){
							$this->db->update_from_array('tbl_commission_settings', array('commsetting_fees' => $val["fees"]), array('smt' => 'commsetting_id = ?', 'vals' => array($row['commsetting_id'])));
							continue;
						}
						else
						$this->db->update_from_array('tbl_commission_settings', array('commsetting_is_deleted' => 1), array('smt' => 'commsetting_id = ?', 'vals' => array($row['commsetting_id'])));
					}
				
					$record = new TableRecord('tbl_commission_settings');
					$arr=array("commsetting_id"=>(int)$val["id"],"commsetting_product"=>$val["product_id"],"commsetting_vendor"=>$val["vendor_id"],"commsetting_category"=>$val["category_id"],"commsetting_fees"=>$val["fees"],"commsetting_is_deleted"=>0,"commsetting_is_mandatory"=>(int)$val["mandatory"]);
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